<?php

declare(strict_types=1);

namespace Gawrys\Counterparty\Enum;

/**
 * What a {@see \Gawrys\Counterparty\Port\RegistryDriver} is able to answer.
 *
 * A driver declares the capabilities it genuinely supports; the verifier routes a
 * required capability to a driver that covers it for the relevant country. When no
 * driver covers a (country, capability) pair the result is inconclusive, never a guess.
 */
enum RegistryCapability: string
{
    /** Whether an entity is an active VAT payer (e.g. PL White List). */
    case VatStatus = 'vat_status';

    /** Whether a bank account belongs to the entity (e.g. PL White List). */
    case BankAccountMatch = 'bank_account_match';

    /** Core legal-entity data: legal name, status, address (e.g. KRS, CEIDG). */
    case LegalEntityData = 'legal_entity_data';

    /** Cross-border EU VAT number validation (e.g. VIES). */
    case EuVatValidation = 'eu_vat_validation';

    /** Beneficial-ownership data (e.g. PL CRBR). */
    case BeneficialOwners = 'beneficial_owners';

    /** Formal business-registration status and identifiers (e.g. KRS, CEIDG). */
    case BusinessRegistration = 'business_registration';

    /** Statistical-register data such as REGON and PKD activity codes (e.g. GUS). */
    case StatisticalData = 'statistical_data';

    public function label(): string
    {
        return match ($this) {
            self::VatStatus => 'VAT status',
            self::BankAccountMatch => 'Bank account match',
            self::LegalEntityData => 'Legal entity data',
            self::EuVatValidation => 'EU VAT validation',
            self::BeneficialOwners => 'Beneficial owners',
            self::BusinessRegistration => 'Business registration',
            self::StatisticalData => 'Statistical data',
        };
    }
}
