function ShowHide(tar, sender) {
    var target = document.getElementById(tar);
    if (sender.textContent.charAt(0) === "◢") {
        target.style.display = "none";
        sender.textContent = "▶" + sender.textContent.substring(1);
    }
    else {
        target.style.display = "block";
        sender.textContent = "◢" + sender.textContent.substring(1);
    }
}