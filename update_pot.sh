#!/bin/sh

l10n_tools="../civi_l10n_tools"

${l10n_tools}/bin/create-pot-files-extensions.sh mutualaid ./ l10n

cat l10n/mutualaid_custom_data.pot >> l10n/mutualaid.pot