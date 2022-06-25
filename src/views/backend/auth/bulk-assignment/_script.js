
$('i.glyphicon-refresh-animate').hide();

function updateItems(r) {
    _opts.assignedUsers = r.assignedUsers;
    search('available');
    search('assigned');
}

$('.btn-assign').click(function (e) {
    var $this = $(this);
    var items = {};
    items.groups = [];
    items.users = [];

    $.each(['available', 'assigned'], function (index, target) {
        $('select.list[data-target="' + target + '"] option:selected').each(function () {
            var type = $(this).closest('optgroup').attr('id');
            if (type === "groups") {
                items.groups.push($(this).val());
            } else if (type === "users") {
                items.users.push($(this).val());
            }
        });

    });

    if (items && (items.users.length || items.groups.length)) {
        $this.children('i.glyphicon-refresh-animate').show();
        $.post($this.attr('href'), { items: items }, function (r) {
            updateItems(r);
        }).always(function () {
            $this.children('i.glyphicon-refresh-animate').hide();
        });
    }
    return false;
});

$('.search[data-target]').keyup(function () {
    search($(this).data('target'));
});


function search(target) {
    var $list = $('select.list[data-target="' + target + '"]');
    $list.html('');
    var q = $('.search[data-target="' + target + '"]').val();

    if (target === "assigned") {
        var optgroups = {
            users: [$('<optgroup id="users" label="' + optgroupLabels.assignedUsers + '">'), false],
        };

        $.each(_opts.assignedUsers, function (index, user) {
            if (user.username.indexOf(q) >= 0) {
                $('<option>').text(user.username).val(user.id_user).appendTo(optgroups['users'][0]);
                optgroups['users'][1] = true;
            }
        });
    } else if (target === "available") {
        var optgroups = {
            groups: [$('<optgroup id="groups" label="' + optgroupLabels.allGroups + '">'), false],
            users: [$('<optgroup id="users" label="' + optgroupLabels.allUsers + '">'), false],
        };

        $.each(_opts.groups, function (index, group) {
            if (group.name.indexOf(q) >= 0) {
                $('<option>').text(group.name).val(group.id_group).appendTo(optgroups['groups'][0]);
                optgroups['groups'][1] = true;
            }
        });

        $.each(_opts.users, function (index, user) {
            if (user.username.indexOf(q) >= 0) {
                $('<option>').text(user.username).val(user.id_user).appendTo(optgroups['users'][0]);
                optgroups['users'][1] = true;
            }
        });
    }

    $.each(optgroups, function () {
        if (this[1]) {
            $list.append(this[0]);
        }
    });

}


// initial
search('available');
search('assigned');
