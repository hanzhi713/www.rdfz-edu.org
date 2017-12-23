function checkPass(form) {
    if (form.password.value === "") {
        alert(getMsg("Password is too long or too short"));
        form.password.focus();
        return false;
    }
    if (form.password.value.length < 6 || form.password.value.length > 20) {
        alert(getMsg("Password is too long or too short"));
        form.password.focus();
        return false;
    }
    if (form.conpass.value === "") {
        alert(getMsg("Please confirm your password!"));
        form.conpass.focus();
        return false;
    }
    if (form.conpass.value !== form.password.value) {
        alert(getMsg("Inconsistent password!"));
        form.conpass.focus();
        return false;
    }
    return true;
}