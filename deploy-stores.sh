#!/usr/bin/env bash

set -Eeuo pipefail

STAGE_DIR="/var/www/html/sax_stage"
OTICA_DIR="/var/www/html/sax_otica"
PLATAFORMA_DIR="/var/www/html/sax_plataforma"
COMMIT_MESSAGE="master"
ASSUME_YES=false
PUBLISH_ONLY=false

usage() {
    cat <<'EOF'
Uso: ./deploy-stores.sh [opções]

Publica o stage no GitHub e atualiza SAX Ótica e SAX Plataforma.

Opções:
  -m, --message TEXTO  Mensagem do commit (padrão: master)
  -y, --yes            Não solicita confirmação
      --publish-only   Faz commit e push do stage, sem atualizar as lojas
  -h, --help           Exibe esta ajuda
EOF
}

while (($# > 0)); do
    case "$1" in
        -m|--message)
            [[ $# -ge 2 ]] || { echo "Falta o texto de --message." >&2; exit 2; }
            COMMIT_MESSAGE="$2"
            shift 2
            ;;
        -y|--yes)
            ASSUME_YES=true
            shift
            ;;
        --publish-only)
            PUBLISH_ONLY=true
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo "Opção desconhecida: $1" >&2
            usage
            exit 2
            ;;
    esac
done

log() {
    printf '\n[%s] %s\n' "$(date '+%H:%M:%S')" "$1"
}

ensure_repository() {
    local repository_dir="$1"

    [[ -d "$repository_dir/.git" ]] || {
        echo "Repositório não encontrado: $repository_dir" >&2
        exit 1
    }
}

ensure_clean_target() {
    local repository_dir="$1"
    local store_name="$2"

    if [[ -n "$(git -C "$repository_dir" status --porcelain)" ]]; then
        echo "$store_name possui alterações locais. O deploy foi interrompido para não sobrescrevê-las:" >&2
        git -C "$repository_dir" status --short >&2
        exit 1
    fi
}

run_migrations() {
    local repository_dir="$1"
    local use_sudo="$2"
    shift 2
    local migrations=("$@")

    if ((${#migrations[@]} == 0)); then
        log "Nenhuma migration nova em $repository_dir"
        return
    fi

    for migration in "${migrations[@]}"; do
        log "Executando migration: $migration"
        if [[ "$use_sudo" == "true" ]]; then
            (cd "$repository_dir" && sudo php artisan migrate --path="$migration" --force)
        else
            (cd "$repository_dir" && php artisan migrate --path="$migration" --force)
        fi
    done
}

clear_laravel_caches() {
    local repository_dir="$1"
    local use_sudo="$2"

    log "Limpando caches em $repository_dir"
    if [[ "$use_sudo" == "true" ]]; then
        (
            cd "$repository_dir"
            sudo php artisan config:clear
            sudo php artisan cache:clear
            sudo php artisan route:clear
            sudo php artisan optimize:clear
        )
    else
        (
            cd "$repository_dir"
            php artisan config:clear
            php artisan cache:clear
            php artisan route:clear
            php artisan optimize:clear
        )
    fi
}

deploy_target() {
    local repository_dir="$1"
    local store_name="$2"
    local branch="$3"
    local before_commit after_commit
    local migrations=()

    ensure_repository "$repository_dir"
    ensure_clean_target "$repository_dir" "$store_name"

    before_commit="$(git -C "$repository_dir" rev-parse HEAD)"

    log "Atualizando $store_name"
    (
        cd "$repository_dir"
        sudo git fetch origin "$branch"
        sudo git pull --ff-only origin "$branch"
    )

    after_commit="$(git -C "$repository_dir" rev-parse HEAD)"

    if [[ "$before_commit" == "$after_commit" ]]; then
        log "$store_name já estava atualizado"
    else
        mapfile -t migrations < <(
            git -C "$repository_dir" diff --name-only --diff-filter=A \
                "$before_commit" "$after_commit" -- 'database/migrations/*.php' | sort
        )
        run_migrations "$repository_dir" true "${migrations[@]}"
    fi

    clear_laravel_caches "$repository_dir" true
    log "$store_name concluído no commit $(git -C "$repository_dir" rev-parse --short HEAD)"
}

ensure_repository "$STAGE_DIR"
ensure_repository "$OTICA_DIR"
ensure_repository "$PLATAFORMA_DIR"

BRANCH="$(git -C "$STAGE_DIR" branch --show-current)"
[[ -n "$BRANCH" ]] || { echo "O stage está em detached HEAD." >&2; exit 1; }

if [[ "$ASSUME_YES" != "true" ]]; then
    printf 'Publicar o stage e atualizar Ótica e Plataforma na branch %s? [s/N] ' "$BRANCH"
    read -r confirmation
    [[ "$confirmation" =~ ^[SsYy]$ ]] || { echo "Deploy cancelado."; exit 0; }
fi

log "Preparando publicação do stage"
cd "$STAGE_DIR"
git add .

stage_migrations=()
mapfile -t stage_migrations < <(
    git diff --cached --name-only --diff-filter=A -- 'database/migrations/*.php' | sort
)
run_migrations "$STAGE_DIR" false "${stage_migrations[@]}"

if git diff --cached --quiet; then
    log "Nenhuma alteração nova para commit"
else
    git commit -m "$COMMIT_MESSAGE"
fi

log "Enviando $BRANCH para o GitHub"
git push origin "$BRANCH"
clear_laravel_caches "$STAGE_DIR" false

if [[ "$PUBLISH_ONLY" == "true" ]]; then
    log "Publicação do stage concluída (--publish-only)"
    exit 0
fi

deploy_target "$OTICA_DIR" "SAX Ótica" "$BRANCH"
deploy_target "$PLATAFORMA_DIR" "SAX Plataforma" "$BRANCH"

log "Deploy concluído em stage, Ótica e Plataforma"
