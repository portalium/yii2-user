function openDeleteModal(e, id) {
    var $modal = $('#modal-user-delete');
    $.pjax.reload({
        container: '#user-delete-pjax',
        url: $modal.data('manage-url') + '?id=' + encodeURIComponent(id),
        type: 'GET',
        push: false,
        replace: false,
        timeout: 10000
    }).done(function () {
        $modal.modal('show');
    });
}

function deleteUser(e) {
    var $modal = $('#modal-user-delete');
    var $form = $('#user-delete-pjax').find('form');
    $(e).prop('disabled', true);
    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        complete: function () {
            window.location.href = $modal.data('index-url');
        }
    });
}
