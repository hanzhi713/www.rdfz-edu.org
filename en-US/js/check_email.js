function checkEmail(form) {
    if (form.email.value === "") {
        alert(getMsg("Please enter your email address!"));
        form.email.focus();
        return false;
    }
    var emailFormat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    if (form.email.value === "" || !emailFormat.test(form.email.value)) {
        alert(getMsg("Invalid email address!"));
        form.email.focus();
        return false;
    }
    return true
}
