var LANG_DIC = {};
function getMsg(msg) {
    if (LANG_DIC[msg] == undefined)
        return msg;
    else
        return LANG_DIC[msg];
}