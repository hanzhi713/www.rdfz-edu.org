function showReply(r, table) {
    for (var i = 0; i < table.rows.length; i++) {
        table.rows[i].style.color = "";
        table.rows[i].style.backgroundColor = "";
        if (table.rows[i].id === "")
            table.rows[i].style.display = "none";
    }
    r.style.backgroundColor = "#007706";
    r.style.color = "#FFFFFF";
    for (i = 1; i <= r.id; i++) {
        var reply = table.rows[r.rowIndex + i];
        reply.style.display = "";
    }
}