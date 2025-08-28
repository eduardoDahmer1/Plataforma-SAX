{{-- STEP 2: DADOS PESSOAIS --}}
<div class="step" id="step2">
    <div class="checkout-box">
        <h4><i class="fa fa-user"></i> Dados Pessoais</h4>
        <label>Nome Completo *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') ?? auth()->user()->name }}">

        <label>Documento *</label>
        <input type="text" name="document" class="form-control"
            value="{{ old('document') ?? auth()->user()->document }}">

        <label>Email *</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') ?? auth()->user()->email }}">

        <label>Telefone *</label>
        <input type="text" name="phone" class="form-control"
            value="{{ old('phone') ?? auth()->user()->phone_number }}">
    </div>
    <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="fa fa-arrow-left"></i>
        Voltar</button>
    <button type="button" class="btn btn-primary" onclick="nextStep(2)"><i class="fa fa-arrow-right"></i>
        Seguir</button>
</div>
