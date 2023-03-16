<?php

namespace App\Constants\RouteNames;

class LiveApiV2
{
    const LIVE_API_V2_ORDER_ASSIGNMENT_STORE                         = 'live_api.v2.order.assignment.store';
    const LIVE_API_V2_ORDER_CANCEL_STORE                             = 'live_api.v2.order.cancel.store';
    const LIVE_API_V2_ORDER_IN_PROGRESS_CANCEL_STORE                 = 'live_api.v2.order.in_progress.cancel.store';
    const LIVE_API_V2_ORDER_IN_PROGRESS_ITEM_ORDER_EXTRA_ITEM_INDEX  = 'live_api.v2.order.in_progress.item_order.extra_item.index';
    const LIVE_API_V2_ORDER_IN_PROGRESS_ITEM_ORDER_EXTRA_ITEM_UPDATE = 'live_api.v2.order.in_progress.item_order.extra_item.update';
    const LIVE_API_V2_ORDER_INDEX                                    = 'live_api.v2.order.index';
    const LIVE_API_V2_ORDER_INVOICE_STORE                            = 'live_api.v2.order.invoice.store';
    const LIVE_API_V2_ORDER_INVOICE_DELETE                           = 'live_api.v2.order.invoice.delete';
    const LIVE_API_V2_ORDER_ITEM_ORDER_CUSTOM_ITEM_DELETE            = 'live_api.v2.order.item_order.custom_item.delete';
    const LIVE_API_V2_ORDER_ITEM_ORDER_CUSTOM_ITEM_INDEX             = 'live_api.v2.order.item_order.custom_item.index';
    const LIVE_API_V2_ORDER_ITEM_ORDER_CUSTOM_ITEM_STORE             = 'live_api.v2.order.item_order.custom_item.store';
    const LIVE_API_V2_ORDER_ITEM_ORDER_PART_INDEX                    = 'live_api.v2.order.item_order.part.index';
    const LIVE_API_V2_ORDER_ITEM_ORDER_PART_SHOW                     = 'live_api.v2.order.item_order.part.show';
    const LIVE_API_V2_ORDER_ITEM_ORDER_PART_UPDATE                   = 'live_api.v2.order.item_order.part.update';
    const LIVE_API_V2_ORDER_ITEM_ORDER_REPLACEMENT_INDEX             = 'live_api.v2.order.item_order.replacement.index';
    const LIVE_API_V2_ORDER_ITEM_ORDER_EXTRA_ITEM_INDEX              = 'live_api.v2.order.item_order.extra_item.index';
    const LIVE_API_V2_ORDER_ITEM_ORDER_EXTRA_ITEM_UPDATE             = 'live_api.v2.order.item_order.extra_item.update';
    const LIVE_API_V2_ORDER_LOG_INDEX                                = 'live_api.v2.order.log.index';
    const LIVE_API_V2_ORDER_SHOW                                     = 'live_api.v2.order.show';
    const LIVE_API_V2_ORDER_UPDATE                                   = 'live_api.v2.order.update';
    const LIVE_API_V2_PART_SHOW                                      = 'live_api.v2.part.show';
    const LIVE_API_V2_SUPPLIER_SHOW                                  = 'live_api.v2.supplier.show';
    const LIVE_API_V2_SUPPLIER_USER_INDEX                            = 'live_api.v2.supplier.user.index';
    /* |--- FALLBACK TO V1 ---| */

    const LIVE_API_V2_ADDRESS_COUNTRY_INDEX                                         = 'live_api.v2.address.country.index';
    const LIVE_API_V2_ADDRESS_COUNTRY_STATE_INDEX                                   = 'live_api.v2.address.country.state.index';
    const LIVE_API_V2_APP_SETTING_SHOW                                              = 'live_api.v2.app_setting.show';
    const LIVE_API_V2_AUTH_EMAIL_FORGOT_PASSWORD_STORE                              = 'live_api.v2.auth.email.forgot_password.store';
    const LIVE_API_V2_AUTH_EMAIL_INITIAL_PASSWORD                                   = 'live_api.v2.auth.email.initial_password';
    const LIVE_API_V2_AUTH_EMAIL_LOGIN                                              = 'live_api.v2.auth.email.login';
    const LIVE_API_V2_AUTH_EMAIL_RESET_PASSWORD_STORE                               = 'live_api.v2.auth.email.reset_password.store';
    const LIVE_API_V2_BRAND_INDEX                                                   = 'live_api.v2.brand.index';
    const LIVE_API_V2_BRAND_SERIES_INDEX                                            = 'live_api.v2.brand.series.index';
    const LIVE_API_V2_BRAND_SERIES_OEM_INDEX                                        = 'live_api.v2.brand.series.oem.index';
    const LIVE_API_V2_CONFIRMED_USER_CONFIRM                                        = 'live_api.v2.confirmed_user.confirm';
    const LIVE_API_V2_CONFIRMED_USER_DELETE                                         = 'live_api.v2.confirmed_user.delete';
    const LIVE_API_V2_CONFIRMED_USER_UPDATE                                         = 'live_api.v2.confirmed_user.update';
    const LIVE_API_V2_LIMITED_SUPPLIER_SHOW                                         = 'live_api.v2.limited_supplier.show';
    const LIVE_API_V2_NOTIFICATION_SETTING_INDEX                                    = 'live_api.v2.notification_setting.index';
    const LIVE_API_V2_NOTIFICATION_SETTING_STORE                                    = 'live_api.v2.notification_setting.store';
    const LIVE_API_V2_OEM_INDEX                                                     = 'live_api.v2.oem.index';
    const LIVE_API_V2_OEM_PART_INDEX                                                = 'live_api.v2.oem.part.index';
    const LIVE_API_V2_OEM_SHOW                                                      = 'live_api.v2.oem.show';
    const LIVE_API_V2_ORDER_COMPLETE_STORE                                          = 'live_api.v2.order.complete.store';
    const LIVE_API_V2_ORDER_DELIVERY_ETA_UPDATE                                     = 'live_api.v2.order.delivery.eta.update';
    const LIVE_API_V2_ORDER_DELIVERY_UPDATE                                         = 'live_api.v2.order.delivery.update';
    const LIVE_API_V2_ORDER_FEE_STORE                                               = 'live_api.v2.order.fee.store';
    const LIVE_API_V2_ORDER_IN_PROGRESS_DELIVERY_CURRI_CONFIRM_STORE                = 'live_api.v2.order.in_progress.delivery.curri.confirm.store';
    const LIVE_API_V2_ORDER_IN_PROGRESS_DELIVERY_CURRI_NOTICE_ENROUTE_CONFIRM_STORE = 'live_api.v2.order.in_progress.delivery.curri.notice.enroute.confirm.store';
    const LIVE_API_V2_ORDER_IN_PROGRESS_DELIVERY_CURRI_PRICE_STORE                  = 'live_api.v2.order.in_progress.delivery.curri.price.store';
    const LIVE_API_V2_ORDER_IN_PROGRESS_DELIVERY_UPDATE                             = 'live_api.v2.order.in_progress.delivery.update';
    const LIVE_API_V2_ORDER_IN_PROGRESS_INDEX                                       = 'live_api.v2.order.in_progress.index';
    const LIVE_API_V2_ORDER_IN_PROGRESS_ITEM_ORDER_INDEX                            = 'live_api.v2.order.in_progress.item_order.index';
    const LIVE_API_V2_ORDER_IN_PROGRESS_ITEM_ORDER_REMOVE_STORE                     = 'live_api.v2.order.in_progress.item_order.remove.store';
    const LIVE_API_V2_ORDER_PRE_APPROVAL_STORE                                      = 'live_api.v2.order.pre_approval.store';
    const LIVE_API_V2_ORDER_REOPEN_STORE                                            = 'live_api.v2.order.reopen.store';
    const LIVE_API_V2_ORDER_SEND_FOR_APPROVAL_STORE                                 = 'live_api.v2.order.send_for_approval.store';
    const LIVE_API_V2_PART_INDEX                                                    = 'live_api.v2.part.index';
    const LIVE_API_V2_PART_RECOMMENDED_REPLACEMENT_STORE                            = 'live_api.v2.part.recommended_replacement.store';
    const LIVE_API_V2_PART_REPLACEMENT_INDEX                                        = 'live_api.v2.part.replacement.index';
    const LIVE_API_V2_REMOVED_USER_DELETE                                           = 'live_api.v2.removed_user.delete';
    const LIVE_API_V2_REMOVED_USER_INDEX                                            = 'live_api.v2.removed_user.index';
    const LIVE_API_V2_REMOVED_USER_STORE                                            = 'live_api.v2.removed_user.store';
    const LIVE_API_V2_SETTING_BULK_NOTIFICATION_STORE                               = 'live_api.v2.setting.bulk_notification.store';
    const LIVE_API_V2_SETTING_INDEX                                                 = 'live_api.v2.setting.index';
    const LIVE_API_V2_SETTING_SHOW                                                  = 'live_api.v2.setting.show';
    const LIVE_API_V2_SUPPLIER_BULK_BRAND_STORE                                     = 'live_api.v2.supplier.bulk_brand.store';
    const LIVE_API_V2_SUPPLIER_BULK_HOUR_STORE                                      = 'live_api.v2.supplier.bulk_hour.store';
    const LIVE_API_V2_SUPPLIER_STAFF_INDEX                                          = 'live_api.v2.supplier.staff.index';
    const LIVE_API_V2_SUPPLIER_UPDATE                                               = 'live_api.v2.supplier.update';
    const LIVE_API_V2_SUPPLIER_USER_SHOW                                            = 'live_api.v2.supplier.user.show';
    const LIVE_API_V2_UNAUTHENTICATED_ORDER_APPROVE_STORE                           = 'live_api.v2.unauthenticated.order.approve.store';
    const LIVE_API_V2_UNAUTHENTICATED_ORDER_ITEM_ORDER_INDEX                        = 'live_api.v2.unauthenticated.order.item_order.index';
    const LIVE_API_V2_UNAUTHENTICATED_ORDER_SHOW                                    = 'live_api.v2.unauthenticated.order.show';
    const LIVE_API_V2_USER_CONFIRM_USER_STORE                                       = 'live_api.v2.user.confirm_user.store';
    const LIVE_API_V2_USER_INDEX                                                    = 'live_api.v2.user.index';
    const LIVE_API_V2_USER_NEW_MESSAGE                                              = 'live_api.v2.user.new_message';
    const LIVE_API_V2_USER_ORDER_INDEX                                              = 'live_api.v2.user.order.index';
}
