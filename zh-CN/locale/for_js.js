var LANG_DIC = {
    "Incorrect validation code!": "验证码不正确！",
    "Please enter your password!": "请输入密码！",
    "Password is too long or too short": "密码太长或太短！",
    "Please confirm your password!": "请确认您的密码！",
    "Inconsistent password!": "密码前后不一致！",
    "Headshot should be jpg and png only!": "头像只能是jpg或png格式的图片！",
    "Please enter your email address!": "请输入您的邮箱地址！",
    "Invalid email address!": "邮箱地址不正确！",
    "Nickname is too short or too long!": "昵称太短或太长！",
    "Poster must be jpg and png only!": "封面只能是jpg或png格式的图片！",
    "Title cannot be empty!" : "标题不能为空！",
    "Author cannot be empty!" : "作者不能为空！"
};
function getMsg(msg) {
    if (LANG_DIC[msg] == undefined)
        return msg;
    else
        return LANG_DIC[msg];
}