function checkExt(name, exts) {
    var index = name.lastIndexOf(".");
    var fileExt = name.substr(index + 1).toLowerCase();
    for (var i = 0; i < exts.length; i++)
        if (fileExt === exts[i]) return true;
    return false;
}