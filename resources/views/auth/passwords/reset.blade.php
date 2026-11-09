<div style="max-width: 400px; margin: 50px auto; text-align: center; font-family: Arial, sans-serif;">
    <h3>Crear Nueva Contraseña</h3>
    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="email" name="correo" value="{{ $correo ?? old('correo') }}" readonly style="width: 100%; padding: 10px; margin-bottom: 15px; background: #f0f0f0;">
        
        <input type="password" name="password" placeholder="Nueva contraseña" required style="width: 100%; padding: 10px; margin-bottom: 15px;">
        <input type="password" name="password_confirmation" placeholder="Confirmar contraseña" required style="width: 100%; padding: 10px; margin-bottom: 15px;">
        
        @error('password') <span style="color: red; display:block; margin-bottom:10px;">{{ $message }}</span> @enderror
        @error('correo') <span style="color: red; display:block; margin-bottom:10px;">{{ $message }}</span> @enderror
        
        <button type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer;">Actualizar Contraseña</button>
    </form>
</div>