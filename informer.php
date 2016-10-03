<?php

require 'XMLParserlog.class.php';
$parser = new XMLParserLog();


@mail('lsavranskiy@redaril.com', $parser->subject, $parser->body . $_GET['data'] . ' ; XMLParserLog line ' . $_GET['line'], 'Data leakage error');