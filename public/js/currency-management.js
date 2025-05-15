$(document).ready(function () {
    function loadCurrencies() {
        $.get('/currencies', function (currencies) {
            let tbody = '';
            currencies.forEach(cur => {
                tbody += `
                    <tr data-id="${cur.id}">
                        <td>${cur.title}</td>
                        <td>${cur.symbol || '-'}</td>
                        <td>${cur.code || '-'}</td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-btn">حذف</button>
                            <button class="btn btn-warning btn-sm edit-btn">ویرایش</button>
                        </td>
                    </tr>`;
            });
            $('#currenciesTable tbody').html(tbody);
        });
    }
    loadCurrencies();

    $('#currencyForm').on('submit', function(e){
        e.preventDefault();
        $.ajax({
            url: '/currencies',
            method: 'POST',
            data: {
                title: $('#curTitle').val(),
                symbol: $('#curSymbol').val(),
                code: $('#curCode').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(){
                $('#curTitle').val('');
                $('#curSymbol').val('');
                $('#curCode').val('');
                loadCurrencies();
            }
        });
    });

    $('#currenciesTable').on('click', '.delete-btn', function() {
        const id = $(this).closest('tr').data('id');
        $.ajax({
            url: '/currencies/' + id,
            method: 'DELETE',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: loadCurrencies
        });
    });

    // ویرایش ارز (نمونه ساده)
    $('#currenciesTable').on('click', '.edit-btn', function() {
        const $tr = $(this).closest('tr');
        const id = $tr.data('id');
        const title = $tr.find('td:nth-child(1)').text();
        const symbol = $tr.find('td:nth-child(2)').text();
        const code = $tr.find('td:nth-child(3)').text();
        $('#curTitle').val(title);
        $('#curSymbol').val(symbol);
        $('#curCode').val(code);
        $('#currencyForm').off('submit').on('submit', function(e){
            e.preventDefault();
            $.ajax({
                url: '/currencies/' + id,
                method: 'PUT',
                data: {
                    title: $('#curTitle').val(),
                    symbol: $('#curSymbol').val(),
                    code: $('#curCode').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(){
                    $('#curTitle').val('');
                    $('#curSymbol').val('');
                    $('#curCode').val('');
                    loadCurrencies();
                    $('#currencyForm').off('submit').on('submit', arguments.callee);
                }
            });
        });
    });
});
