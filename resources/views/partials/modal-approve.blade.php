<form action="#" id="approve_form" method="GET">
    {{ csrf_field() }}
    <div class="modal modal-success fade" tabindex="-1" id="approve-modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #2ecc71; color: white; border-bottom: none;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color: white; opacity: 1;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" style="font-weight: bold;">
                        <i class="fa-solid fa-check-circle"></i> Aprobación de Préstamo
                    </h4>
                </div>
                <div class="modal-body" style="padding: 30px;">
                    <div class="text-center">
                        <div style="width: 80px; height: 80px; background: #e8f8f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <i class="fa-solid fa-thumbs-up" style="font-size: 40px; color: #2ecc71;"></i>
                        </div>
                        
                        <h3 style="margin-top: 0; margin-bottom: 10px; color: #333; font-weight: 600;">
                            ¿Está seguro de aprobar?
                        </h3>
                        
                        <p class="text-muted" style="font-size: 14px; margin-bottom: 20px;">
                            Al aprobar este préstamo, se habilitará para su entrega. Esta acción registrará su usuario como responsable de la aprobación.
                        </p>

                        <div class="alert alert-warning" style="font-size: 12px; text-align: left; margin-bottom: 0; background-color: #fcf8e3; border-color: #faebcc; color: #8a6d3b;">
                            <i class="fa-solid fa-triangle-exclamation"></i> <strong>Nota:</strong> Asegúrese de haber verificado todos los requisitos antes de continuar.
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f1f1f1; padding: 15px 30px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="margin-right: 10px;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success btn-approve-submit" style="background-color: #2ecc71; border-color: #2ecc71; font-weight: bold; padding-left: 20px; padding-right: 20px;">
                        <i class="fa-solid fa-check"></i> Sí, Aprobar
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.getElementById('approve_form').addEventListener('submit', function() {
        var btn = this.querySelector('.btn-approve-submit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Aprobando...';
    });
</script>