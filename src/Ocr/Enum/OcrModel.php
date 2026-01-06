<?php

declare(strict_types=1);

namespace PhpVision\YandexVision\Ocr\Enum;

enum OcrModel: string
{
    case Page = 'page';
    case PageColumnSort = 'page-column-sort';
    case Handwritten = 'handwritten';
    case Table = 'table';
    case Markdown = 'markdown';
    case MathMarkdown = 'math-markdown';
    case Passport = 'passport';
    case DriverLicenseFront = 'driver-license-front';
    case DriverLicenseBack = 'driver-license-back';
    case VehicleRegistrationFront = 'vehicle-registration-front';
    case VehicleRegistrationBack = 'vehicle-registration-back';
    case LicensePlates = 'license-plates';
}
