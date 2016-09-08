<?php

namespace Yoghi\Bundle\MaddaBundleTest\Utils;

use SLLH\StyleCIBridge\ConfigBridge;
use Symfony\CS\ConfigurationResolver;
use Symfony\CS\FileCacheManager;
use Symfony\CS\Fixer;
use Symfony\CS\LintManager;

trait FileCompare
{
    /**
     * Compare generated class with expected class into resource dir
     *
     * @param string         $resourcesDir     fullPath resources dir
     * @param string         $namespace        namespace of class
     * @param string         $className        class name
     * @param string         $directoryOutput  output directory to compare from
     * @param bool           $createIfNotExist generate file if not exist equals on genereted one
     * @param ClassGenerator $g                class generator object to test
     */
    private function compareClassPhp($resourcesDir, $namespace, $className, $directoryOutput, $createIfNotExist = false)
    {
        $fileExpected = $resourcesDir.'/'.$className.'.php';
        $fileName = $className.'.php';
        $fileActual = $directoryOutput.'/'.$namespace.'/'.$fileName;

        // echo file_get_contents($fileActual);

        /** @var \Symfony\CS\Config\Config $config */
        $config = ConfigBridge::create(__DIR__.'/../../');

        $config->setUsingCache(false);

        $fixer = new Fixer();
        $fixer->registerBuiltInFixers();
        $fixer->registerBuiltInConfigs();
        // $fixer->setLintManager(new LintManager());
        $fixer->registerCustomFixers($config->getCustomFixers());

        $cresolver = new ConfigurationResolver();
        $cresolver->setConfig($config);
        $cresolver->setAllFixers($fixer->getFixers());
        $cresolver->setOption('level', 'symfony');
        // $cresolver->setOption('fixers', 'eof_ending,strict_param,short_array_syntax,trailing_spaces,indentation,line_after_namespace,php_closing_tag');
        $cresolver->setOption('fixers', 'binary_operator_spaces,blank_line_after_namespace,blank_line_after_opening_tag,blank_line_before_return,
                                         braces,cast_spaces,class_definition,concat_without_spaces,declare_equal_normalize,elseif,encoding,
                                         full_opening_tag,function_declaration,function_typehint_space,hash_to_slash_comment,heredoc_to_nowdoc,
                                         include,lowercase_cast,lowercase_constants,lowercase_keywords,method_argument_space,method_separation,
                                         native_function_casing,new_with_braces,no_alias_functions,no_blank_lines_after_class_opening,
                                         no_blank_lines_after_phpdoc,no_closing_tag,no_empty_phpdoc,no_empty_statement,
                                         no_extra_consecutive_blank_lines,no_leading_import_slash,no_leading_namespace_whitespace,
                                         no_multiline_whitespace_around_double_arrow,no_short_bool_cast,no_singleline_whitespace_before_semicolons,
                                         no_spaces_after_function_name,no_spaces_inside_offset,no_spaces_inside_parenthesis,no_tab_indentation,
                                         no_trailing_comma_in_list_call,no_trailing_comma_in_singleline_array,no_trailing_whitespace,
                                         no_trailing_whitespace_in_comment,no_unneeded_control_parentheses,no_unreachable_default_argument_value,
                                         no_unused_imports,no_whitespace_before_comma_in_array,no_whitespace_in_blank_line,normalize_index_brace,
                                         object_operator_without_whitespace,phpdoc_align,phpdoc_annotation_without_dot,phpdoc_indent,
                                         phpdoc_inline_tag,phpdoc_no_access,phpdoc_no_empty_return,phpdoc_no_package,phpdoc_scalar,
                                         phpdoc_separation,phpdoc_single_line_var_spacing,phpdoc_to_comment,phpdoc_trim,
                                         phpdoc_type_to_var,phpdoc_types,phpdoc_var_without_name,pre_increment,print_to_echo,psr4,self_accessor,
                                         short_scalar_cast,simplified_null_return,single_blank_line_at_eof,single_blank_line_before_namespace,
                                         single_class_element_per_statement,single_import_per_statement,single_line_after_imports,single_quote,
                                         space_after_semicolon,standardize_not_equals,switch_case_semicolon_to_colon,switch_case_space,
                                         ternary_operator_spaces,trailing_comma_in_multiline_array,trim_array_spaces,unalign_double_arrow,
                                         unalign_equals,unary_operator_spaces,unix_line_endings,visibility_required,whitespace_after_comma_in_array,
                                         short_array_syntax,linebreak_after_opening_tag,ordered_imports,no_useless_return,phpdoc_order,ordered_use,
                                         -phpdoc_short_description');

        $cresolver->resolve();

        $config->fixers($cresolver->getFixers());

        // $fileCacheManager = new FileCacheManager(false, $directoryOutput, $cresolver->getFixers());
        $iFile = new SplFileInfo($fileActual, $directoryOutput.'/'.$namespace, $fileName);
        // $fixer->fixFile($iFile, $cresolver->getFixers(), false, false, $fileCacheManager);

        $config->finder(new \ArrayIterator([$iFile]));

        // $changed =
        $fixer->fix($config, false, false);

        $fileActualFixed = $iFile->getPathname();
        $actual = file_get_contents($fileActualFixed);

        if (!file_exists($fileExpected) && $createIfNotExist) {
            file_put_contents($fileExpected, $actual);
        }

        $expected = file_get_contents($fileExpected);

        $this->assertSame($expected, $actual, 'Classe '.$className.' invalid');
    }

    private function compareFile($resourcesDir, $directoryOutput, $pathFie, $createIfNotExist = false)
    {
        $fileInput = $resourcesDir.'/'.$pathFie;
        $actual = file_get_contents($directoryOutput.'/'.$pathFie);

        if (!file_exists($fileInput) && $createIfNotExist) {
            file_put_contents($fileInput, $actual);
        }

        $expected = file_get_contents($fileInput);

        $this->assertSame($expected, $actual, 'File '.$pathFie.' invalid');
    }
}
