function validateHeadshot(file) {
    var ext = file.value;
    if (!checkExt(ext, ['jpg', 'jpeg', 'png'])) {
        alert(getMsg("Headshot should be jpg and png only!"));
        file.focus();
        return false;
    }
    return true;
}