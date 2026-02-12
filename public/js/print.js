async function printTicket(url, request, fallbackUrl, type) {
    // alert(request.loan_day[0].loan_day_agents[0].transaction_id);
    // alert(url);
    const printServiceUrl = url;
    toastr.options.escapeHtml = false;

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 2000); // Timeout de 2 segundos

    // window.open(`${fallbackUrl}/${request.id}`, "Recibo", `width=700, height=700`)

    try {
        // Intenta alcanzar el servicio. 'no-cors' es para una simple verificación de conectividad.
        await fetch(printServiceUrl, { signal: controller.signal, mode: 'no-cors' });
        clearTimeout(timeoutId);

        console.log(`✅ El servicio de impresión en ${printServiceUrl} está ACTIVO.`);

        if(type == 'LoanPayment')
        {        
            // Construir el array de detalles para el servicio de impresión
            const details = request.loan_day.map(item => {
                return {
                    late: item.late?'SI':'NO',
                    date: item.date,
                    amount: item.loan_day_agents[0].amount,
                };
            });

            // Construir el objeto de datos para enviar
            const data = {
                template: 'Capresi_loanPayment',
                code: request.code,
                ci: request.people.ci,
                customer : request.people.first_name + ' ' + request.people.last_name1 + ' ' + request.people.last_name2,
                datePayment : request.loan_day[0].loan_day_agents[0].created_at,
                codePayment : request.loan_day[0].loan_day_agents[0].transaction.id,
                register : request.loan_day[0].loan_day_agents[0].agent.name,

                details: details
            };

            // Enviar los datos al servicio de impresión
            await fetch(`${printServiceUrl}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            });
        }
        if(type == 'LoanComprobante')
        {       
            const data = {
                template: 'Capresi_loanVouche',
                code: request.code,
                dateLoan: request.dateDelivered,
                ci: request.people.ci? request.people.ci: 'Sin CI ',
                customer : request.people.first_name + ' ' + request.people.last_name1 + ' ' + request.people.last_name2,
                amountLoan: request.amountLoan,
                amountPorcentage: request.amountPorcentage,
                amountTotal: request.amountTotal,
                register : request.agent_delivered.name,
                dateStart : request.loan_day[0].date,
                dateFinish : request.loan_day[request.loan_day.length - 1].date,
            };

            console.log(data);

            //Enviar los datos al servicio de impresión
            await fetch(`${printServiceUrl}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            });
        }
        console.log('✅ Datos enviados al servicio de impresión correctamente.');
        toastr.success('Imprimiendo...', '<i class="fa fa-print"></i> Exito...');
    } catch (error) {
        clearTimeout(timeoutId);
        console.error(`❌ No se pudo conectar al servicio de impresión en ${printServiceUrl}. Imprimiendo desde el navegador.`, error.message);
        console.log('Abriendo ventana de impresión del navegador...');
        // alert(sale.id);

        if(type == 'LoanPayment')
        {
            window.open(`${fallbackUrl}/${request.id}/${request.loan_day[0].loan_day_agents[0].transaction_id}`, "Recibo", `width=700, height=700`);
        }

        if(type == 'LoanComprobante')
        {
            window.open(`${fallbackUrl}/${request.id}`, "Recibo", `width=700, height=700`);
        }


        toastr.warning('No se pudo conectar al servicio de impresión. Usando impresión del navegador.', '<i class="fa fa-exclamation-triangle"></i> Advertencia');
    }
}