<?php

    if (empty($argv[1])) {
        throw new Exception('1st parameter must be the file path.');
    }

    /**
     * Importing the sprint csv file in a array.
     */
    $filePath = $argv[1];
    $file = fopen($filePath,"r");
    print_r("Importing the sprint csv file « " . $filePath . " »…\n");
    while(! feof($file))
    {
        $aParsedRawCsv[] = fgetcsv($file);
    }
    fclose($file);

    // Deleting the empty line.
    // 1) The columns header of Vivify.
    unset($aParsedRawCsv[0]);
    end($aParsedRawCsv);

    // 2) A empty line at the end of the file.
    $latestKey = key($aParsedRawCsv);
    unset($aParsedRawCsv[$latestKey]);

    print_r("Finally imported.\n");

    /**
     * Parsing elementary information.
     */
    print_r("Parsing elementary information…\n");
    foreach ($aParsedRawCsv as $csvColumn) {
        $aElementaryInformation[$csvColumn[1]] = array(
            "cost"        => $csvColumn[7],
            "title"       => $csvColumn[2],
            "description" => $csvColumn[3],
            "labels"      => explode(',', $csvColumn[11]),
            "parentId"    => $csvColumn[21]
        );
    }
    print_r("Parsing done.\n");

    /**
     * Agregate by parent (one-level maximum supported).
     */
    print_r("Agregating by parent…\n");
    foreach ($aElementaryInformation as $itemId => $properties) {
        if(empty($properties["parentId"])) {
            unset($properties['parentId']);
            $aAgregatedByParent[$itemId] = $properties;
        } else {
            $aAgregatedByParent[$properties['parentId']]['childs'][$itemId] = $properties;
        }
    }
    print_r("Agregate done.\n");

    /**
     * Markdown formate.
     */
    print_r("Markdown formating…\n");

    // Table of content.
    $markdownGiantString = "[TOC]\n\n";

    // Parents.
    foreach ($aAgregatedByParent as $parentItemId => $parentProperties) {

        // Header.
        $markdownGiantString .= "## " . $parentItemId . " (" . $parentProperties["cost"] . "pts) "
            . $parentProperties["title"] . "\n\n";

        // Content.
        $markdownGiantString .= $parentProperties["description"] . "\n\n\n---\n\n\n";

        // Childs.
        if(isset($parentProperties['childs'])) {
            foreach ($parentProperties['childs'] as $childItemId => $childProperties) {

                // Header.
                $markdownGiantString .= "### " . $childItemId . " (" . $childProperties["cost"] . "pts) "
                    . $childProperties["title"] . "\n\n";

                // Content.
                $markdownGiantString .= $childProperties["description"] . "\n\n\n---\n\n\n";

            }
        }

    }
    print_r("Markdown formated.\n");

    /**
     * Markdown formate.
     */
    print_r("Markdown export…\n");
    file_put_contents ( 'export.md', $markdownGiantString);
    print_r("Markdown exported.\n");
    print_r("Finish ! :).\n");
