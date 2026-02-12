<form action="#" id="deliverMoney_form" method="POST">
    {{ csrf_field() }}
    <div class="modal modal-success fade" data-backdrop="static" id="deliver-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa-solid fa-money-check-dollar"></i> Entregar Dinero</h4>
                </div>
                <div class="modal-footer">    
                    <div class="text-center" style="text-transform:uppercase">
                        <i class="fa-solid fa-money-check-dollar" style="color: rgb(68, 68, 68); font-size: 5em;"></i>
                        <br>
                            
                        <p><b>Desea entregar el dinero al cliente?</b></p>
                        {{-- <br>
                        <label for="dateDelivered">Fecha de entrega</label>
                        <input type="date" name="dateDelivered" class="form-control" required>
                        <br> --}}
                    </div>
                    <div id="progress-container" style="display: none; margin-top: 15px;">
                        <small>Procesando...</small>
                        <div class="progress">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <button type="submit" id="btn-submit-delivered" style="display:block" class="btn btn-success pull-right delete-confirm">Sí, entregar</button>
                    <button type="button" class="btn btn-default pull-right btn-cancel-delivered" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</form>