<?php
/**
 * Corrige egulias/email-validator 2.1.3 com PHP 7.4
 * (Composer 1 não consegue baixar versões >= 2.1.25 no Packagist).
 */
$root = dirname(__DIR__);
$target = $root.'/vendor/egulias/email-validator/EmailValidator/Parser/Parser.php';

if (! is_file($target)) {
    fwrite(STDERR, "Parser.php não encontrado — rode composer install antes.\n");
    exit(0);
}

$src = file_get_contents($target);
if ($src === false) {
    fwrite(STDERR, "Não foi possível ler {$target}\n");
    exit(1);
}

if (strpos($src, "isset(\$previous['type'])") !== false) {
    echo "Patch egulias/email-validator já aplicado\n";
    exit(0);
}

$old = <<<'PHP'
        if ($previous['type'] === EmailLexer::S_BACKSLASH
            &&
            $this->lexer->token['type'] !== EmailLexer::GENERIC
        ) {
PHP;

$new = <<<'PHP'
        if (isset($previous['type'])
            && $previous['type'] === EmailLexer::S_BACKSLASH
            &&
            $this->lexer->token['type'] !== EmailLexer::GENERIC
        ) {
PHP;

if (strpos($src, $old) === false) {
    fwrite(STDERR, "Trecho esperado não encontrado em {$target}\n");
    exit(1);
}

file_put_contents($target, str_replace($old, $new, $src));
echo "Patch egulias/email-validator (PHP 7.4) aplicado em {$target}\n";
