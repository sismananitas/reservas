@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card p-3 mt-3 col-md-5">
        <h1>Método de pago</h1>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Error</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('change.payment.update', ['reservation' => $reservation->id]) }}" method="post" autocomplete="off">
            @csrf @method('PUT')
            
            <div class="form-group">
                <label for="tipo_pago">Tipo</label>

                <select class="form-control" name="tipo_pago" id="select_payment" onchange="changePayment()">
                    <option value="">- Elegir -</option>
                    <option value="tarjeta" @if($reservation->payment_method == 'tarjeta') selected @endif>Tarjeta</option>
                    <option value="efectivo" @if($reservation->payment_method == 'efectivo') selected @endif>Efectivo</option>
                    <option value="deposito" @if($reservation->payment_method == 'deposito') selected @endif>Depósito</option>
                </select>
            </div>

            <div class="tarjeta-details" @if($reservation->payment_method !== 'tarjeta') style="display: none" @endif>
                <div class="form-group">
                    <label for="numero_tarjeta">Numero de tarjeta</label>
                    <input class="form-control" type="text" name="numero_tarjeta" @if ($reservation->payment_method == 'tarjeta') value="{{ $reservation->credit_card->number }}" @endif>
                </div>
                
                <div class="form-group">
                    <label for="vencimiento">Vencimiento</label>
                    <input class="form-control" type="text" name="vencimiento" placeholder="mm/yy" @if ($reservation->payment_method == 'tarjeta') value="{{ $reservation->credit_card->expiration }}" @endif>
                </div>

                <div class="form-group">
                    <label for="codigo_seguridad">Código de seguridad</label>
                    <input class="form-control" type="text" name="codigo_seguridad" placeholder="CCV" @if ($reservation->payment_method == 'tarjeta') value="{{ $reservation->credit_card->security_code }}" @endif>
                </div>

                <div class="form-group">
                    <label for="titular">Titular</label>
                    <input class="form-control" type="text" name="titular" placeholder="Nombre y Apellido" @if ($reservation->payment_method == 'tarjeta') value="{{ $reservation->credit_card->holder }}" @endif>
                </div>
            </div>

            <button class="btn btn-warning" type="submit"><i class="fas fa-share"></i> Editar</button>
            <a class="btn btn-secondary" href="{{ route('home') }}">Cancelar</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script defer>
        function changePayment() {
            let payment = select_payment.value

            if (payment == 'tarjeta') {
                document.querySelector('.tarjeta-details').style.display = 'block';
            } else document.querySelector('.tarjeta-details').style.display = 'none';
        }
    </script>
@endpush