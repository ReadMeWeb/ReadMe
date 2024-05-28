/* --- COMMON FUNCTIONS --- */

function getErrorMessage(msg) {
    let errMsg = document.createElement('p');
    errMsg.innerHTML = msg;
    errMsg.className = 'errorMessage';
    return errMsg;
}

function clearErrorMessages() {
    let errors = Array.from(document.getElementsByClassName('errorMessage'));
    errors.forEach(err => {
        err.remove();
    });
}

function validateFile(fileName, input, isNeeded) {
    let extensions = new Array("jpg", "png", "jpeg");
    let fileExt = fileName.split('.').pop(); 

    if(isNeeded && fileName === '') {
        input.insertAdjacentElement(
            'afterend',
            getErrorMessage('Si prega di fornire un file.')
        );
        return false;
    }

    if(fileName !== '' && !extensions.find((ext) => ext==fileExt)) {
        input.insertAdjacentElement(
            'afterend',
            getErrorMessage(`Estensione del file non valida. L'estensione del file deve essere tra queste: ${extensions.toString()}.`)
        );
        return false;
    }
    return true;
}

function validateString(str, input, min=Number.MIN_SAFE_INTEGER, max=Number.MAX_SAFE_INTEGER, trim=true) {
    str = trim ? str.trim() : str;

    if(str == '') {
        input.insertAdjacentElement(
            'afterend',
            getErrorMessage('Si prega di fornire un valore.')
        );
        return false;
    }

    if(str.length < min) {
   
        input.insertAdjacentElement(
                'afterend', 
                getErrorMessage(`Si prega di fornire un valore con un numero di caratteri maggiori di ${min}.`));
        
        return false;
    }

    if(str.length > max) {
        input.insertAdjacentElement(
            'afterend', 
            getErrorMessage(`Si prega di fornire un valore con un numero di caratteri minore di ${max}.`));
        return false;
    }   

    return true;
}

function validateNumber(num, input, min=Number.MIN_SAFE_INTEGER, max=Number.MAX_SAFE_INTEGER) {
    if(num < min) {
   
        input.insertAdjacentElement(
                'afterend', 
                getErrorMessage(`Si prega di fornire un valore numerico maggiore di ${min}.`));
        
        return false;
    }

    if(num > max) {
        input.insertAdjacentElement(
            'afterend', 
            getErrorMessage(`Si prega di fornire un valore numerico minore di ${max}.`));
        return false;
    }   

    return true;
}

/* --- ACCOUNT PAGE --- */

function validateAccountInfo() {
    
    clearErrorMessages();

    let usernameInput = document.getElementById('username');
    let passwordInput = document.getElementById('password');
    
    return Array.from([
        validateString(usernameInput.value, usernameInput, 4, 20),
        validateString(passwordInput.value, passwordInput, 4, 120, false)
    ]).find((e) => e===false) === undefined;



}

/* --- BOOK PAGE --- */

function validateBookInfo(edit) {
    clearErrorMessages();

    let titleInput = document.getElementById('input-title');
    let authorInput = document.getElementById('input-author');
    let descInput = document.getElementById('input-description');
    let coverInput = document.getElementById('input-cover');
    let copiesInput = document.getElementById('input-no-copies');

    return Array.from([
        validateString(titleInput.value, titleInput, 4, 255),
        validateString(authorInput.options[authorInput.selectedIndex].text, authorInput, 4, 255),
        validateString(descInput.value, descInput, 20),
        validateNumber(copiesInput.value, copiesInput, 1),
        validateFile( coverInput.value, coverInput, !edit)

    ]).find((e)=> e===false) === undefined;
}


/* --- LOAN PAGE --- */
function validateLoanInfo() {

    clearErrorMessages();

    let res = true;

    let startDateInput = document.getElementById("i");
    let startDate = new Date(startDateInput.value);

    let endDateInput = document.getElementById("f");
    let endDate = new Date(endDateInput.value);

    let today = new Date(new Date().toDateString());
    let dateDiff = Math.round((endDate.getTime() - startDate.getTime()) / (24 * Math.pow(60,2) * 1000));

    if(startDate < today) {
        startDateInput.insertAdjacentElement('afterend', getErrorMessage('La data di inizio non può essere prima di oggi'));
        res = false;
    }

    if(endDate <=  startDate) {
        endDateInput.insertAdjacentElement('afterend', getErrorMessage('La data di fine deve essere dopo la data di inizio.'));
        res = false;
    }
    
    if(res) {
        if(dateDiff > 30) {
            endDateInput.insertAdjacentElement('afterend', getErrorMessage('La data di fine può essere al massimo 30 giorni dopo la data di inizio.'))
            res = false;
        }
        else if(dateDiff < 7) {
            endDateInput.insertAdjacentElement('afterend', getErrorMessage('La data di fine può essere al massimo 30 giorni dopo la data di inizio.'))
            res = false;
        }
        
    }
    
    return res;
}



