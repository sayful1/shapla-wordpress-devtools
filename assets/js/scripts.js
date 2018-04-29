(function ($) {
    'use strict';

    var _tableCellEditor = $('#tableCellValue');

    $('.wp-list-table').find('td').each(function () {
        var _tableCell = $(this);
        var _tableRow = $(this).closest('tr');
        _tableCell.on('dblclick', function () {

            var _rowInfo = _tableRow.find('.row-info');
            var _primaryKey = _rowInfo.data('primary-key');
            var _primaryValue = _rowInfo.data('primary-value');
            var _columnName = _tableCell.data('colname');

            $('.modal-title-column').text(_columnName);
            $('#tableCellEditor').css('display', 'block');
            $.ajax({
                method: 'POST',
                url: ajaxurl,
                data: {
                    action: 'get_cell_data',
                    nonce: 'nonce',
                    table: $('input[name="table"]').val(),
                    column: _columnName,
                    primary_key: _primaryKey,
                    primary_value: _primaryValue
                },
                success: function (response) {
                    var _responseData = response.data;
                    _tableCellEditor.data('table', _responseData.table);
                    _tableCellEditor.data('primary-key', _responseData.primary_key);
                    _tableCellEditor.data('primary-value', _responseData.primary_value);
                    _tableCellEditor.data('column-name', _responseData.column);
                    _tableCellEditor.val(_responseData.column_value);
                },
                error: function (response) {
                    $('#tableCellEditor').css('display', 'none');
                }
            });
        });
    });

    $('.modal').on('click', '.modal-btn-confirm', function (event) {
        event.preventDefault();

        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'update_cell_data',
                nonce: 'nonce',
                table: _tableCellEditor.data('table'),
                column: _tableCellEditor.data('column-name'),
                primary_key: _tableCellEditor.data('primary-key'),
                primary_value: _tableCellEditor.data('primary-value'),
                column_value: _tableCellEditor.val()
            },
            success: function (response) {
                var _responseData = response.data;
                var _recordRow = $("#record_id-" + _responseData.primary_value).closest('tr');
                var _recordColumn = _recordRow.find('td.column-' + _responseData.column);

                _recordColumn.text(_responseData.column_value);
                $('#tableCellEditor').css('display', 'none');
            }
        }).done(function (response) {
            alert(response.data.success_msg);
        });
    })

    // Modal
    $('.modal-close').on('click', function () {
        $(this).closest('.modal').css('display', 'none');
    });
    $('.modal-btn-cancel').on('click', function () {
        $(this).closest('.modal').css('display', 'none');
    });

})(jQuery);