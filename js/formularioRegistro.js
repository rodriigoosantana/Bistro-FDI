document.addEventListener('DOMContentLoaded', function () {
  const formRegistro = document.getElementById('formRegistro');
  if (!formRegistro) {
    return;
  }

  const campoNombreUsuario = formRegistro.querySelector('#nombreUsuario');
  const estadoNombreUsuario = formRegistro.querySelector('#estadoNombreUsuario');

  if (!campoNombreUsuario || !estadoNombreUsuario) {
    return;
  }

  const minLongitud = 4;
  const checkUrl = campoNombreUsuario.dataset.checkUrl;
  const originalNombre = (campoNombreUsuario.dataset.originalUsername || '').trim().toLowerCase();
  let debounceId = null;
  let peticionActiva = null;

  const actualizarEstadoNombreUsuario = function (tipo, mensaje) {
    estadoNombreUsuario.className = 'username-status';

    if (tipo) {
      estadoNombreUsuario.classList.add('is-' + tipo);
    }

    estadoNombreUsuario.textContent = mensaje || '';
  };

  const comprobarNombreUsuario = function () {
    const nombreUsuario = campoNombreUsuario.value.trim();
    const nombreUsuarioNormalizado = nombreUsuario.toLowerCase();

    if (debounceId) {
      clearTimeout(debounceId);
    }

    if (peticionActiva) {
      peticionActiva.abort();
    }

    if (nombreUsuario.length < minLongitud) {
      actualizarEstadoNombreUsuario('', '');
      campoNombreUsuario.setCustomValidity('');
      return;
    }

    if (originalNombre !== '' && nombreUsuarioNormalizado === originalNombre) {
      actualizarEstadoNombreUsuario('ok', 'Es tu nombre de usuario actual.');
      campoNombreUsuario.setCustomValidity('');
      return;
    }

    actualizarEstadoNombreUsuario('pending', 'Comprobando disponibilidad...');
    campoNombreUsuario.setCustomValidity('Comprobando disponibilidad...');

    debounceId = setTimeout(function () {
      peticionActiva = new AbortController();

      fetch(checkUrl + '?nombreUsuario=' + encodeURIComponent(nombreUsuario) + '&original=' + encodeURIComponent(originalNombre), {
        signal: peticionActiva.signal,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(function (respuesta) {
          if (!respuesta.ok) {
            throw new Error('No se pudo comprobar el nombre de usuario');
          }
          return respuesta.json();
        })
        .then(function (data) {
          if (data.available) {
            actualizarEstadoNombreUsuario('ok', 'Nombre de usuario disponible.');
            campoNombreUsuario.setCustomValidity('');
          } else {
            actualizarEstadoNombreUsuario('error', 'Ese nombre de usuario ya existe.');
            campoNombreUsuario.setCustomValidity('Ese nombre de usuario ya existe.');
          }
        })
        .catch(function (error) {
          if (error.name === 'AbortError') {
            return;
          }

          actualizarEstadoNombreUsuario('error', 'No se pudo comprobar ahora mismo.');
          campoNombreUsuario.setCustomValidity('');
        });
    }, 450);
  };

  campoNombreUsuario.addEventListener('input', comprobarNombreUsuario);
  campoNombreUsuario.addEventListener('blur', comprobarNombreUsuario);
});
