<form action="#" id="approve_form" method="GET">
    {{ csrf_field() }}
    <div class="modal modal-success fade" data-backdrop="static" id="approve-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #46be8a; color: white;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="fa-solid fa-circle-check"></i> Confirmar Aprobación
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="text-center" style="text-transform: uppercase;">
                        <div style="font-size: 5em; color: #46be8a; margin-bottom: 15px;">
                            <i class="fa-solid fa-check-to-slot"></i>
                        </div>
                        <h3 style="margin-top: 0; color: #333;">
                            <strong>¿CONFIRMAR APROBACIÓN?</strong>
                        </h3>
                        <p style="color: #666;">
                            El registro será marcado como aprobado. ¿Desea continuar?
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success pull-right confirm-approve" style="background-color: #46be8a; border-color: #46be8a;">
                        <i class="fa-solid fa-thumbs-up"></i> Sí, Aprobar
                    </button>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">
                        <i class="fa-solid fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>