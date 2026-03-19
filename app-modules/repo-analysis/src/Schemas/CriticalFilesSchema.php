<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Schemas;

use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class CriticalFilesSchema
{
    public static function build(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'critical_files',
            description: 'Seleção dos arquivos mais críticos do repositório para análise',
            properties: [
                new ArraySchema(
                    name: 'files',
                    description: 'Uma lista com os caminhos dos arquivos mais críticos do repositório',
                    items: new StringSchema('path', 'Caminho do arquivo')
                ),
            ],
            requiredFields: ['files']
        );
    }
}
