<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Helper;

class GatewayActions
{
    const CONFIRMATION_PAGE_ROUTE = 'sistecredito/Gateway/Confirm';

    const SISTECREDITO_PAYMENT_ACCEPTED_STATE = 3;

    const TOKEN_GENERATED = "tokenGenerated";
    const REST_API_REQUEST_SENT = "startCredit";
    const VALIDATE_ORDER = "validateOrder";

    const VERIFY_DOCUMENT_ID_DOCUMENT_TYPE = "verifyDocumentId/documentType";

    const GET_SISTECREDITO_ORDER_LOG = "getSistecreditoOrderLog";
    const VALIDATE_JWT_REQUEST_EXCEPTION = "validateJwtRequestException";
    const CONFIRMATION_PAYMENT_EXCEPTION = "confirmationPaymentException";
    const VALIDATE_REQUEST_PARAMS = "validateRequestParams";
    const VALIDA_STATUS_TRANSACTION = "validaStatusTransaction";
    const VALIDATE_REQUEST_JWT = "validateRequestJwt";
    const VALIDATE_AMOUNT_TRANSACTION = "validateAmountTransaction";
    const CONFIRMATION_PAYMENT = "confirmationPayment";

    const GET_INFO_CREDIT = "getInfoCredit";

    const ERRORS = [
        "800" => [
            "message" => "ErrorGeneral",
            "description" => "Ocurrió un error general.",
            "public" => false,
            "style" => "error"
        ],
        "801" => [
            "message" => "CreditRequestInProcess",
            "description" => "Ya hay una solicitud en proceso, por favor intente más tarde.",
            "public" => true,
            "style" => "error"
        ],
        "802" => [
            "message" => "AmountNotValid",
            "description" => "El valor del crédito solicitado es menor al valor mínimo para un crédito definido por Sistecrédito.",
            "public" => false,
            "style" => "error"
        ],
        "804" => [
            "message" => "OrderIdTooLarge",
            "description" => "El valor de la orden es demasiado larga.",
        ],
        "805" => [
            "message" => "IdDocumentNotValid",
            "description" => "El documento enviado excede el máximo número de caracteres admitidos para una identificación de usuario o también contiene un formato no permitido para una identificación en Sistecrédito.",
            "public" => false,
            "style" => "error"
        ],
        "806" => [
            "message" => "DocumentTypeNotValid",
            "description" => "la longitud del tipo de identificación proporcionada esta fuera del rango de longitud permitido o su formato no es reconocido para Sistecrédito. ",
            "public" => false,
            "style" => "error"
        ],
        "811" => [
            "message" => "DelayToCancelMustBeGreater",
            "description" => "El tiempo está por debajo de lo mínimo permitido por Sistecrédito.",
            "public" => false,
            "style" => "error"
        ],
        "812" => [
            "message" => "DelayToCancelMustBeLess",
            "description" => "El tiempo está por encima de lo máximo permitido por sistecredito.",
            "public" => false,
            "style" => "error"
        ],
        "813" => [
            "message" => "ValueToPayMustBeGreater",
            "description" => "El valor esta vacio o este es menor a cero.",
            "public" => false,
            "style" => "error"
        ],
        "814" => [
            "message" => "VendorIdMustNotBeEmpty",
            "description" => "El vendorid proporcionado por sistecredito no puede estar nulo.",
            "public" => false,
            "style" => "error"
        ],
        "815" => [
            "message" => "VendorIdWrongFormat",
            "description" => "El formato del vendorid no es el correcto puede deberse a caracteres inapropiados.",
            "public" => false,
            "style" => "error"
        ],
        "816" => [
            "message" => "StoreIdMustNotBeEmpty",
            "description" => "El vendorid proporcionado por Sistecrédito no puede estar nulo o vacío.",
            "public" => false,
            "style" => "error"
        ],
        "817" => [
            "message" => "StoreIdWrongFormat",
            "description" => "El vendorid proporcionado por Sistecrédito tiene un formato inapropiado.",
            "public" => false,
            "style" => "error"
        ],
        "818" => [
            "message" => "RespondeURLMustNotBeEmpty",
            "description" => "La url de respuesta no puede estar vacía o nula.",
            "public" => false,
            "style" => "error"
        ],
        "819" => [
            "message" => "RespondeURLWrongFormat",
            "description" => "La url no es adecuada para responder la petición con los datos de la transacción.",
            "public" => false,
            "style" => "error"
        ],
        "820" => [
            "message" => "OrderIdMustNotBeNullOrEmpty",
            "description" => "No se proporcionó OrderId.",
            "public" => false,
            "style" => "error"
        ],
        "821" => [
            "message" => "ResponseUrlTooLarge",
            "description" => "La url de confirmación es demasiado larga.",
            "public" => false,
            "style" => "error"
        ],
        "822" => [
            "code" => "822",
            "message" => "ConfirmationsParamsInvalid",
            "description" => "Parametros inválidos, no es posible realizar la confirmación correctamente.",
            "public" => false,
            "style" => "error"
        ],
        "823" => [
            "code" => "823",
            "message" => "ConfirmationsTransactionStatusInvalid",
            "description" => "La transacción no fue confirmada exitosamente, estado de transacción inválido.",
            "public" => false,
            "style" => "error"
        ],
        "824" => [
            "code" => "824",
            "message" => "ConfirmationJwtContentInvalid",
            "description" => "El contenido del JWT es diferente a el esperado.",
            "public" => false,
            "style" => "error"
        ],
        "825" => [
            "code" => "825",
            "message" => "ConfirmationJwtCatch",
            "description" => "Error validando el JWT.",
            "public" => false,
            "style" => "error"
        ],
        "826" => [
            "code" => "826",
            "message" => "ConfirmationValueNotMatch",
            "description" => "El valor recibido en la confirmación no corresponde al valor de la orden.",
            "public" => false,
            "style" => "error"
        ],
        "827" => [
            "code" => "827",
            "message" => "ConfirmationOrderLogNotFound",
            "description" => "No se encontro la orden en los logs de sistecredito.",
            "public" => false,
            "style" => "error"
        ],
    ];

}
