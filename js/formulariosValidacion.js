document.addEventListener('DOMContentLoaded', function () {
  const formularios = document.querySelectorAll('form[id^="form"]');
  if (!formularios.length) {
    return;
  }

  formularios.forEach(function (formulario) {
    const camposRequeridos = formulario.querySelectorAll('input[required], select[required], textarea[required]');

    camposRequeridos.forEach(function (campo) {
      const marcarTocado = function () {
        campo.classList.add('touched-field');
      };

      campo.addEventListener('blur', marcarTocado);
      campo.addEventListener('input', marcarTocado);
      campo.addEventListener('change', marcarTocado);
    });

    const campoPassword = formulario.querySelector('#password');
    const campoPassword2 = formulario.querySelector('#password2');

    if (campoPassword && campoPassword2) {
      const validarCoincidenciaPasswords = function () {
        const password = campoPassword.value;
        const password2 = campoPassword2.value;

        if (password2 === '') {
          campoPassword2.setCustomValidity('');
          return;
        }

        if (password === password2) {
          campoPassword2.setCustomValidity('');
          return;
        }

        campoPassword2.setCustomValidity('Los passwords deben coincidir');
      };

      campoPassword.addEventListener('input', validarCoincidenciaPasswords);
      campoPassword2.addEventListener('input', validarCoincidenciaPasswords);
      campoPassword2.addEventListener('blur', validarCoincidenciaPasswords);
    }
  });
});
