// Estado de validación
let validacionActual = null;
let validacionTimeout = null;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    initSolicitudesForm();
});

// Inicializar formulario de solicitudes
function initSolicitudesForm() {
    const btnNew = document.getElementById('btn-new-solicitud');
    const modalNueva = document.getElementById('modal-nueva-solicitud');
    const closeModalNueva = document.getElementById('close-modal-nueva');
    const btnCancelarNueva = document.getElementById('btn-cancelar-nueva');
    const formNueva = document.getElementById('form-nueva-solicitud');

    // Abrir modal de nueva solicitud
    if (btnNew) {
        btnNew.addEventListener('click', function() {
            modalNueva.classList.remove('hidden');
            cargarDiasDisponibles();
        });
    }

    // Cerrar modal
    if (closeModalNueva) {
        closeModalNueva.addEventListener('click', function() {
            cerrarModalNueva();
        });
    }

    if (btnCancelarNueva) {
        btnCancelarNueva.addEventListener('click', function() {
            cerrarModalNueva();
        });
    }

    // Cerrar al hacer clic fuera
    if (modalNueva) {
        modalNueva.addEventListener('click', function(e) {
            if (e.target === modalNueva) {
                cerrarModalNueva();
            }
        });
    }

    // Validación en tiempo real
    const tipoPermiso = document.getElementById('tipo-permiso');
    const fechaInicio = document.getElementById('fecha-inicio');
    const fechaFin = document.getElementById('fecha-fin');
    const motivo = document.getElementById('motivo');

    [tipoPermiso, fechaInicio, fechaFin, motivo].forEach(field => {
        if (field) {
            field.addEventListener('change', validarFormularioTiempoReal);
            field.addEventListener('blur', validarFormularioTiempoReal);
        }
    });

    // Submit del formulario
    if (formNueva) {
        formNueva.addEventListener('submit', handleSubmitSolicitud);
    }
}

// Cerrar modal y limpiar
function cerrarModalNueva() {
    const modalNueva = document.getElementById('modal-nueva-solicitud');
    const formNueva = document.getElementById('form-nueva-solicitud');

    if (modalNueva) modalNueva.classList.add('hidden');
    if (formNueva) formNueva.reset();
    limpiarValidacion();
}

// Cargar días disponibles del usuario
async function cargarDiasDisponibles() {
    try {
        const res = await fetch('api/validar_solicitud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                tipo: 'vacaciones',
                fecha_inicio: '',
                fecha_fin: ''
            })
        });

        if (res.ok) {
            const data = await res.json();
            if (data.dias_disponibles) {
                const diasDisp = data.dias_disponibles.total || 0;
                const periodosCompletos = data.dias_disponibles.periodos_completos || 0;
                const diasGanados = data.dias_disponibles.ganados || 0;
                const diasUsados = data.dias_disponibles.usados || 0;

                document.getElementById('dias-disponibles-numero').textContent = diasDisp.toFixed(1);
                document.getElementById('periodos-info').textContent =
                    `${periodosCompletos} período${periodosCompletos !== 1 ? 's' : ''} completado${periodosCompletos !== 1 ? 's' : ''} • ${diasGanados} ganados - ${diasUsados} usados`;
                document.getElementById('info-dias-disponibles').classList.remove('hidden');
            }
        }
    } catch (err) {
        console.error('Error cargando días disponibles:', err);
    }
}

// Validación en tiempo real
async function validarFormularioTiempoReal() {
    // Debounce para evitar muchas llamadas
    if (validacionTimeout) {
        clearTimeout(validacionTimeout);
    }

    validacionTimeout = setTimeout(async () => {
        const tipo = document.getElementById('tipo-permiso').value;
        const fechaInicio = document.getElementById('fecha-inicio').value;
        const fechaFin = document.getElementById('fecha-fin').value;
        const motivo = document.getElementById('motivo').value;
        const documentoAdjunto = document.getElementById('documento-adjunto');
        const tieneDocumento = documentoAdjunto && documentoAdjunto.files.length > 0;

        // Si no hay datos mínimos, limpiar
        if (!tipo || !fechaInicio || !fechaFin) {
            limpiarValidacion();
            return;
        }

        try {
            const res = await fetch('api/validar_solicitud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    tipo,
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin,
                    motivo,
                    tiene_documento: tieneDocumento
                })
            });

            if (!res.ok) throw new Error('Error en validación');

            const data = await res.json();
            validacionActual = data;

            // Mostrar días hábiles calculados
            if (data.dias_habiles > 0) {
                document.getElementById('dias-habiles-calculados').textContent = data.dias_habiles;
                document.getElementById('info-dias-calculados').classList.remove('hidden');
            } else {
                document.getElementById('info-dias-calculados').classList.add('hidden');
            }

            // Mostrar aprobador
            if (data.aprobador_rol) {
                const aprobadorTexto = data.aprobador_rol === 'rrhh' ?
                    'Recursos Humanos' : 'Jefe Inmediato';
                document.getElementById('aprobador-texto').textContent = aprobadorTexto;
                document.getElementById('info-aprobador').classList.remove('hidden');
            } else {
                document.getElementById('info-aprobador').classList.add('hidden');
            }

            // Mostrar documento requerido
            if (data.requiere_documento) {
                let tipoDocTexto = 'Este tipo de permiso requiere documentación';
                if (data.tipo_documento) {
                    tipoDocTexto = 'Requiere: ' + data.tipo_documento.replace(/_/g, ' ');
                }
                document.getElementById('tipo-documento-texto').textContent = tipoDocTexto;
                document.getElementById('info-documento').classList.remove('hidden');
            } else {
                document.getElementById('info-documento').classList.add('hidden');
            }

            // Mostrar disponibilidad de departamento
            if (data.disponibilidad_departamento) {
                const disp = data.disponibilidad_departamento;
                const porcentaje = disp.porcentaje_disponible || 0;
                const esValido = disp.valido;

                const colorClass = esValido ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                const iconColor = esValido ? 'text-green-600' : 'text-red-600';
                const textColor = esValido ? 'text-green-900' : 'text-red-900';

                const html = `
                    <div class="${colorClass} border rounded-xl p-4">
                        <div class="flex items-center">
                            <i class="fas fa-users ${iconColor} text-xl mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold ${textColor}">Disponibilidad del departamento</p>
                                <p class="text-lg font-bold ${textColor}">${porcentaje.toFixed(0)}% disponible</p>
                                <p class="text-xs ${iconColor}">${disp.disponibles || 0} de ${disp.total_empleados || 0} empleados disponibles</p>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('info-disponibilidad-dept').innerHTML = html;
                document.getElementById('info-disponibilidad-dept').classList.remove('hidden');
            } else {
                document.getElementById('info-disponibilidad-dept').classList.add('hidden');
            }

            // Mostrar errores
            const errorList = document.getElementById('error-list');
            const validationErrors = document.getElementById('validation-errors');
            if (data.errores && data.errores.length > 0) {
                errorList.innerHTML = data.errores.map(e => `<li>${e}</li>`).join('');
                validationErrors.classList.remove('hidden');
            } else {
                validationErrors.classList.add('hidden');
            }

            // Mostrar warnings
            const warningList = document.getElementById('warning-list');
            const validationWarnings = document.getElementById('validation-warnings');
            if (data.warnings && data.warnings.length > 0) {
                warningList.innerHTML = data.warnings.map(w => `<li>${w}</li>`).join('');
                validationWarnings.classList.remove('hidden');
            } else {
                validationWarnings.classList.add('hidden');
            }

        } catch (err) {
            console.error('Error en validación:', err);
        }
    }, 500); // Esperar 500ms después del último cambio
}

// Limpiar validación
function limpiarValidacion() {
    validacionActual = null;
    document.getElementById('info-dias-calculados').classList.add('hidden');
    document.getElementById('info-aprobador').classList.add('hidden');
    document.getElementById('info-documento').classList.add('hidden');
    document.getElementById('info-disponibilidad-dept').classList.add('hidden');
    document.getElementById('validation-errors').classList.add('hidden');
    document.getElementById('validation-warnings').classList.add('hidden');
}

// Manejar submit de solicitud
async function handleSubmitSolicitud(e) {
    e.preventDefault();

    // Validar antes de enviar
    await validarFormularioTiempoReal();

    if (validacionActual && !validacionActual.valido) {
        alert('Por favor corrige los errores antes de enviar la solicitud');
        return;
    }

    const formNueva = document.getElementById('form-nueva-solicitud');
    const formData = new FormData(formNueva);
    const documentoAdjunto = document.getElementById('documento-adjunto');

    // Si hay documento, agregarlo
    if (documentoAdjunto && documentoAdjunto.files.length > 0) {
        formData.append('documento', documentoAdjunto.files[0]);
    }

    try {
        const btnSubmit = document.getElementById('btn-submit-solicitud');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';

        const res = await fetch('api/solicitud_create.php', {
            method: 'POST',
            body: formData
        });

        if (!res.ok) throw new Error('Error al crear solicitud');

        const data = await res.json();

        if (data && data.success) {
            alert('Solicitud enviada exitosamente');
            cerrarModalNueva();
            location.reload();
        } else {
            alert(data.message || 'Error al crear solicitud');
        }
    } catch (err) {
        console.error(err);
        alert('Error de red al crear solicitud');
    } finally {
        const btnSubmit = document.getElementById('btn-submit-solicitud');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Enviar Solicitud';
    }
}
