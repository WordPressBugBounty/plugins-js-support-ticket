function uploadfileInternalnote(fileobj, filesizeallow, fileextensionallow) {
    return uploadfile(fileobj, filesizeallow, fileextensionallow);
}
function uploadfile(fileobj, filesizeallow, fileextensionallow) {
    // The dialog was cancelled: nothing chosen, nothing to check.
    if (!fileobj.files || !fileobj.files.length) {
        return true;
    }
    var file = fileobj.files[0];
    var name = file.name;
    var size = (file.size) / 1024; //kb
    var fileext = getExtension(name);
    if (size > filesizeallow) {
        return jsstRefuseFile(fileobj, jQuery('span#filesize').html());
    }
    var f_e_a = fileextensionallow.split(','); // file extension allow array
    var isfileextensionallow = checkExtension(f_e_a, fileext);
    if (isfileextensionallow == 'N') {
        return jsstRefuseFile(fileobj, jQuery('span#fileext').html());
    }
    return true;
}
/* A refused file is cleared in place. The field used to be swapped for new
   markup that was always named filename[] and brought a second remove cross,
   so after one refused file the internal note's attachment was posted as a
   reply attachment, and the box showed two crosses. (5.0.4) */
function jsstRefuseFile(fileobj, message) {
    fileobj.value = '';
    jsstShowChosenFiles();
    alert(message);
    return false;
}
function  checkExtension(f_e_a, fileext) {
    var match = 'N';
    for (var i = 0; i < f_e_a.length; i++) {
        if (f_e_a[i].toLowerCase() === fileext.toLowerCase()) {
            match = 'Y';
            break;
        }
    }
    return match;
}
function getExtension(filename) {
    return filename.split('.').pop().toLowerCase();
}

/* The browser's own file box cuts the chosen name to "..." when the box is
   narrow (three to a row on the reply form), so nobody could tell whether a
   file was attached. Each attachment area lists the chosen files, by name and
   size, under its boxes. Names go in as text, never as HTML. (5.0.4) */
function jsstFileSize(bytes) {
    var kb = bytes / 1024;
    return kb < 1024 ? Math.max(1, Math.round(kb)) + ' KB' : (kb / 1024).toFixed(1) + ' MB';
}
function jsstShowChosenFiles() {
    var wrappers = document.querySelectorAll('.tk_attachment_value_wrapperform');
    for (var w = 0; w < wrappers.length; w++) {
        var wrapper = wrappers[w];
        var list = wrapper.nextElementSibling;
        if (list && !list.classList.contains('tk_attachment_chosen')) {
            list = null;
        }
        var files = [];
        var inputs = wrapper.querySelectorAll('input[type=file]');
        for (var i = 0; i < inputs.length; i++) {
            for (var j = 0; inputs[i].files && j < inputs[i].files.length; j++) {
                files.push(inputs[i].files[j]);
            }
        }
        if (!files.length) {
            if (list) {
                list.parentNode.removeChild(list);
            }
            continue;
        }
        if (!list) {
            list = document.createElement('div');
            list.className = 'tk_attachment_chosen';
            wrapper.parentNode.insertBefore(list, wrapper.nextSibling);
        }
        list.textContent = '';
        for (var f = 0; f < files.length; f++) {
            var item = document.createElement('span');
            item.className = 'tk_attachment_chosen_file';
            item.title = files[f].name;
            item.textContent = files[f].name + ' (' + jsstFileSize(files[f].size) + ')';
            list.appendChild(item);
        }
    }
}
// After the field's own onchange check, which may have cleared the file.
document.addEventListener('change', function (e) {
    if (e.target && e.target.type === 'file' && e.target.closest && e.target.closest('.tk_attachment_value_wrapperform')) {
        jsstShowChosenFiles();
    }
});
// The pages' own handlers remove the box; the list follows once they have.
document.addEventListener('click', function (e) {
    if (e.target && e.target.closest && e.target.closest('.tk_attachment_remove')) {
        setTimeout(jsstShowChosenFiles, 0);
    }
});
document.addEventListener('reset', function () {
    setTimeout(jsstShowChosenFiles, 0);
});
