$('.add-detail').on('click', function () {
    let parent = $(this).parent().parent()
    parent.find('tbody').append('<tr>' + parent.find('tr.table-row:last').html() + '</tr>')
    parent.find('tr.table-row:last td').each(function () {
        $(this).val("")
    })
    parent.find('.row-total').appendTo(parent.find('tbody'))
    parent.find('.typeahead:last').typeahead({
        hint: true,
        highlight: true,
        minLength: 1
    },
        {
            name: 'states',
            source: substringMatcher(states)
        }
    )
})

$(document).on('click', '.close-modal', function(){
    $('#' + $(this).attr('data-modal')).modal('hide');
})

$(".add-detail").click(function (e) {
    $(".select2-container").addClass("z-index-1");
    $(".select2-modal")
        .next()
        .removeClass("z-index-1");
    $(".select2-modal")
        .next()
        .css("width", "100%");
});

$(".close-modal").click(function (e) {
    setTimeout(() => {
        $(".select2-container").removeClass("z-index-1");
    }, 500);
});

$(document).on('keyup', '.harga-detail, .jumlah-detail', function() {
    let parentRow = $(this).parent().parent()
    let parentTable = $(this).parent().parent().parent()

    // if ($(this).hasClass('jumlah-detail')) {
    //     let jumlahTemp = 0
    //     let jumlahRow = parentRow.find('.jumlah-detail').val() ? parentRow.find('.jumlah-detail').val() : 0
    //     let jumlahTotal = parentRow.find('.total-jumlah').val() ? parentRow.find('.total-jumlah').val() : 0
    //     parentTable.find('.jumlah-detail').each(function(){
    //         jumlahTemp += parseInt($(this).val())
    //     })
    //     if (jumlahTemp > jumlahTotal) {
    //         alert("Jumlah tidak boleh lebih dari " + jumlahTotal)
    //         parentRow.find('.jumlah-detail').val(jumlahTotal - (jumlahTemp - jumlahRow))
    //     }
    // }

    let jumlah = parentRow.find('.jumlah-detail').val() ? parentRow.find('.jumlah-detail').val() : 0
    let harga = parentRow.find('.harga-detail').val() ? parentRow.find('.harga-detail').val() : 0
    parentRow.find('.subtotal-detail').val(jumlah * harga)

    let subTotalAkhir = 0
    parentTable.find('.subtotal-detail').each(function(){
        subTotalAkhir += parseInt($(this).val())
    })
    parentTable.find('.subtotal-akhir').val(subTotalAkhir)

    let grandTotal = 0
    $(document).find('.subtotal-akhir').each(function(){
        grandTotal += parseInt($(this).val())
    })
    $('#grand-total').val(grandTotal)
})

$(document).on('change', '.supplier-nama', function(){
    if ($(this).val()) {
        let parent = $(this).parent().parent().parent()
        $.ajax({
            url: "rb/getKodeSupplier",
            type: "post", //form method
            data: {
                nama: $(this).val()
            },
            dataType: "text",
            beforeSend: function () { },
            success: function (result) {
                console.log(result)
                parent.find('.supplier-kode').val(result)
            },
            complete: function (result) { },
            error: function (xhr, Status, err) { }
        })
    }
})

$(document).on('click', '.delete-row', function () {
    // alert($('#table-body tr').length)
    let parent = $(this).parent().parent().parent().parent()
    // console.log(parent.html())
    if (parent.find('tbody tr').length > 2) {
        // alert("Detailsss")
        let c = confirm("Apakah yakin akan menghapus data?")
        if (c)
            $(this).parent().parent().remove()
    }
    else {
        alert("Detail harus ada")
    }
})

var substringMatcher = function (strs) {
    return function findMatches(q, cb) {
        var matches, substringRegex;

        // an array that will be populated with substring matches
        matches = [];

        // regex used to determine if a string contains the substring `q`
        substrRegex = new RegExp(q, 'i');

        // iterate through the pool of strings and for any string that
        // contains the substring `q`, add it to the `matches` array
        $.each(strs, function (i, str) {
            if (substrRegex.test(str)) {
                matches.push(str);
            }
        });

        cb(matches);
    };
};

var states = $('#stored-val').data('val')

// console.log($('#stored-val').data('val'))

$('.typeahead').typeahead({
    hint: true,
    highlight: true,
    minLength: 1
},
    {
        name: 'states',
        source: substringMatcher(states)
    });