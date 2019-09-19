import Axios from "axios";
import Swal from "sweetalert2";

export class FormEditarReserva
{
    constructor(formName) {
        this.form = document.getElementById(formName)
    }

    successMessage(successObj) {
        console.log(successObj)
        console.log(successObj.data)
        Swal.fire({
            type: 'success',
            title: 'Completado'
        })
        .then(() => {
            //location.reload();
        })
    }

    errorMessage(errorObj) {
        let errors = errorObj.response.data.errors
        let errorTitle = errorObj.response.data.message
        let errorText = ''

        for (let i in errors) {
            errorText += errors[i][0] + '<br>'
        }
        Swal.fire({
            type: 'error',
            title: 'Datos inválidos',
            html: errorText
        });
        console.log(errorObj);
    }

    sendData(url, data) {
        Axios.post(url, data)
        .then(response => {
            this.successMessage(response.data)
        })
        .catch(errors => {
            this.errorMessage(errors)
        })
    }

    submitEventListener() {
        this.form.addEventListener('submit', e => {
            e.preventDefault();
            let data = new FormData(e.target);
            let url = e.target.action;
   
            this.sendData(url, data);
        });
    }
}