{{-- STEP 3: ENDEREÇO --}}
<div class="step" id="step3">
    <div class="checkout-box">
        <h4><i class="fa fa-map-marker-alt"></i> Endereço de Envio</h4>
        <label><input type="radio" name="shipping" value="1" checked> Enviar para o Endereço
            Cadastrado</label><br>
        <label><input type="radio" name="shipping" value="2"> Endereço Alternativo</label><br>
        <label><input type="radio" name="shipping" value="3"> Retirar na Loja</label><br>

        {{-- Endereço Cadastrado --}}
        <p>
            <i class="fa fa-home"></i> Endereço:
            <strong>
                {{ auth()->user()->address ?? '' }},
                {{ auth()->user()->additional_info ?? '' }},
                {{ auth()->user()->city ?? '' }},
                {{ auth()->user()->state ?? '' }}
            </strong>
        </p>

        {{-- Endereço Alternativo --}}
        <div id="nc_addressShipping" style="display:none;">
            <label>Escolha o País</label>
            <select name="country" id="country" class="form-control">
                <option value="brasil">Brasil</option>
                <option value="paraguai">Paraguai</option>
            </select>

            <div id="brasil_address" style="display:none;">
                <label>CEP</label>
                <input type="text" name="cep" class="form-control" placeholder="CEP">
                <label>Rua</label>
                <input type="text" name="street" class="form-control" placeholder="Rua">
                <label>Número</label>
                <input type="text" name="number" class="form-control" placeholder="Número da casa">
                <label>Observações</label>
                <input type="text" name="observations" class="form-control" placeholder="Observações">
            </div>

            <div id="paraguai_address" style="display:none;">
                <label>Código da Casa</label>
                <input type="text" name="house_code" class="form-control" placeholder="Código da casa">
                <label>Observações</label>
                <input type="text" name="observations" class="form-control" placeholder="Observações">
            </div>
        </div>

        {{-- Retirar na Loja --}}
        <div id="storePickUp" style="display:none;">
            <p><i class="fa fa-store"></i> Escolha a Loja:</p>
            <select name="store" id="storeSelect" class="form-control">
                <option value="1">SAX Ciudad del Este</option>
                <option value="2">SAX Assunção</option>
            </select>

            <div class="mt-3" id="storeMaps">
                <div class="store-map" data-store="1" style="display:block;">
                    <div class="ratio ratio-16x9">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3600.8882062800003!2d-54.60985242460801!3d-25.508774677511763!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94f69aaaec5ef03d%3A0xff12a8b090a63ebd!2sSAX%20Department%20Store!5e0!3m2!1spt-BR!2spy!4v1755633236261!5m2!1spt-BR!2spy"
                            style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                <div class="store-map" data-store="2" style="display:none;">
                    <div class="ratio ratio-16x9">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1818.5976216080874!2d-57.564660298266034!3d-25.284507459303036!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x945da8a8f48ce025%3A0x2715791645730d75!2sSAX!5e0!3m2!1spt-BR!2spy!4v1755633297166!5m2!1spt-BR!2spy"
                            style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Troca de mapa conforme a loja selecionada
            const storeSelect = document.getElementById('storeSelect');
            const storeMaps = document.querySelectorAll('.store-map');

            storeSelect.addEventListener('change', function() {
                const value = this.value;
                storeMaps.forEach(map => {
                    map.style.display = map.dataset.store === value ? 'block' : 'none';
                });
            });
        </script>
    </div>
    <button type="button" class="btn btn-secondary" onclick="prevStep(2)"><i class="fa fa-arrow-left"></i>
        Voltar</button>
    <button type="button" class="btn btn-primary" onclick="nextStep(3)"><i class="fa fa-arrow-right"></i>
        Seguir</button>
</div>
