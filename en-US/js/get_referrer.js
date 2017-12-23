function getReferrer() {
    var ref = document.createElement('input');
    ref.type = 'hidden';
    ref.name = 'ref';
    ref.value = document.referrer;
    document.getElementById('loginform').appendChild(ref);
}