<!-- Puedes extender de tu layout principal si lo deseas -->
<div style="max-width: 400px; margin: 50px auto; text-align: center; font-family: Arial, sans-serif;">
    <h3>Recuperar Acceso</h3>
    @if(session('success')) <p style="color: green;">{{ session('success') }}</p> @endif
    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <input type="email" name="correo" placeholder="Tu correo clínico" required style="width: 100%; padding: 10px; margin-bottom: 15px;">
        @error('correo') <span style="color: red; display:block; margin-bottom:10px;">{{ $message }}</span> @enderror
        <button type="submit" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; cursor: pointer;">Enviar enlace</button>
    </form>
    <br><a href="{{ url('/') }}">Volver al login</a>
</div>