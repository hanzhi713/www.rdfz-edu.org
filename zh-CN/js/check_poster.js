function checkPoster(form) {
    if (form.photo.value !== "") {
        if (!checkExt(form.photo.value, ["jpg", "jpeg", "png"])) {
            alert(getMsg("Poster must be jpg and png only!"));
            return false;
        }
    }
    return true;
}