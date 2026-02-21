<form action="#" class="form-edit-add" id="approve_form" method="GET">
    {{ csrf_field() }}
    <div class="modal modal-success fade" data-backdrop="static" id="approve-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="color:rgb(255, 255, 255) !important"><i class="fa-solid fa-check-circle"></i> Aprobación de Préstamo</h4>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-cancel" data-dismiss="modal" style="margin-right: 10px;">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success btn-submit">
                        Sí, Aprobar
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>