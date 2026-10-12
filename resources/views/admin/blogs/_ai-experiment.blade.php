@if(app()->environment(['local', 'staging']) && config('services.openai.blog_experiment_enabled'))
<section class="sax-premium-card shadow-sm mb-4" id="blogAiExperiment"
    data-generate-url="{{ route('admin.blogs.ai-experiment.generate') }}">
    <div class="p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <span class="badge bg-warning text-dark mb-2">EXPERIMENTAL · SOLO STAGE</span>
                <h5 class="fw-bold mb-1">Generar artículo con IA</h5>
                <p class="text-muted small mb-0">Investiga en Internet y completa los campos del formulario. Revisá el artículo y guardalo cuando esté listo.</p>
            </div>
        </div>

        <div id="blogAiExperimentForm">
            <div class="row g-3">
                <div class="col-12">
                    <label for="blogAiTopic" class="sax-label">Tema</label>
                    <input id="blogAiTopic"  class="form-control sax-input" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label for="blogAiFocus" class="sax-label">Producto / enfoque</label>
                    <input id="blogAiFocus"  class="form-control sax-input" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label for="blogAiInstructions" class="sax-label">Instrucciones adicionales</label>
                    <textarea id="blogAiInstructions"  class="form-control sax-input" rows="3" maxlength="2000"></textarea>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-dark" id="blogAiGenerateButton">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Generar y completar
                    </button>
                    <span class="small text-muted ms-3 d-none" id="blogAiProgress">
                        <i class="fas fa-spinner fa-spin me-1"></i>Investigando y generando artículo…
                    </span>
                </div>
            </div>
        </div>

        <div id="blogAiResult" class="mt-4 d-none" aria-live="polite"></div>
    </div>
</section>
@endif
