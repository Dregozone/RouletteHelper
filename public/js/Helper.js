
function recordNumber() {

    let number = document.getElementById("number").value;

    if ( number >= 1 && number <= 36 ) {
        window.location.href = "?write=" + number;
    } else {
        // This number is invalid
    }
}

function resetData() {

    window.location.href = "?reset=1";
}
