/* --- COMMON FUNCTIONS --- */

function getErrorMessage(msg) {
    let errMsg = document.createElement('p');
    errMsg.innerHTML = msg;
    errMsg.className = 'errorMessage';
    return errMsg;
}

function clearErrorMessage(input) {
    let sibling = input.nextElementSibling;

    if(sibling && sibling.className == 'errorMessage') {
        sibling.remove();
    }
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
                getErrorMessage(`Si prega di fornire un valore numerico maggiore di ${min-1}.`));

        return false;
    }

    if(num > max) {
        input.insertAdjacentElement(
            'afterend',
            getErrorMessage(`Si prega di fornire un valore numerico minore di ${max-1}.`));
        return false;
    }

    return true;
}

/* --- ACCOUNT PAGE --- */

function validateAccountInfo() {
    return Array.from([
        validateUsername(),
        validatePassword()
    ]).find((e) => e===false) === undefined;

}

function validateUsername() {
    let usernameInput = document.getElementById('username');
    clearErrorMessage(usernameInput);
    return validateString(usernameInput.value, usernameInput, 4, 20);
}

function validatePassword() {
    let passwordInput = document.getElementById('password');
    clearErrorMessage(passwordInput);
    return validateString(passwordInput.value, passwordInput, 4, 120, false);
}


/* --- BOOK PAGE --- */

function validateBookInfo(edit) {

    return Array.from([
        validateTitle(),
        validateAuthor(),
        validateDesc(),
        validateCopiesNumber(),
        validateCover(edit)

    ]).find((e)=> e===false) === undefined;
}

function validateTitle() {
    let titleInput = document.getElementById('input-title');
    clearErrorMessage(titleInput);
    return validateString(titleInput.value, titleInput, 4, 30);
}

function validateAuthor() {

    let authorInputDiv = document.getElementById('author-input')
    let authorInput = document.getElementById('input-author');
    let newAuthorInput = document.getElementById('input-author-new');

    clearErrorMessage(authorInputDiv);

    if(newAuthorInput.value.trim().length !== 0) {
        return validateString(newAuthorInput.value, authorInputDiv, 4, 30);
    }
    return validateString(authorInput.options[authorInput.selectedIndex].text, authorInputDiv, 4, 30);
}

function validateDesc() {
    let  descInput = document.getElementById('input-description');
    clearErrorMessage(descInput);
    return validateString(descInput.value, descInput, 20);
}

function validateCopiesNumber() {
    let copiesInput = document.getElementById('input-no-copies');
    clearErrorMessage(copiesInput);
    return validateNumber(copiesInput.value, copiesInput, 1, 1000000);
}

function validateCover(edit) {
    let coverInput = document.getElementById('input-cover');
    clearErrorMessage(coverInput);
    return validateFile( coverInput.value, coverInput, !edit);
}


/* --- LOAN PAGE --- */
function validateLoanInfo() {

    let endDateInput = document.getElementById("f");
    clearErrorMessage(endDateInput);

    let res = true;

    let endDate = new Date(endDateInput.value);

    let startDate = new Date(new Date().toDateString());
    let dateDiff = Math.round((endDate.getTime() - startDate.getTime()) / (24 * Math.pow(60,2) * 1000));

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
            endDateInput.insertAdjacentElement('afterend', getErrorMessage('La data di fine deve essere almeno 7 giorni dopo la data di inizio.'))
            res = false;
        }

    }

    return res;
}
