<script>
$(function () {
    $('#body-editor').summernote({
        height: 280,
        lang: 'pt-BR',
        placeholder: 'Digite o conteúdo do alerta...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    $('#scheduled-email-form').on('submit', function () {
        $('#body-editor').val($('#body-editor').summernote('code'));
    });
});
</script>
