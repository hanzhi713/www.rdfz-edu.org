function checkSummary(form){
    if (form.title.value === "") {
        alert(getMsg("Title cannot be empty!"));
        form.title.focus();
        return false;
    }
    if (form.author.value === "") {
        alert(getMsg("Author cannot be empty!"));
        form.author.focus();
        return false;
    }
    return true;
}