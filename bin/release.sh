#!/usr/bin/env bash
# Pubblica una nuova versione del plugin.
#
#   bin/release.sh 1.0.1 "Descrizione breve delle modifiche"
#   bin/release.sh 1.0.1 "..." --dry-run     # mostra le modifiche senza fare commit
#
# 1. controlla di essere su main, senza modifiche in sospeso e allineati a GitHub
# 2. aggiorna la versione in gb-popup.php (intestazione + GB_POPUP_VERSION) e readme.txt (Stable tag)
# 3. aggiunge la descrizione al changelog di readme.txt (se la voce per la versione non c'è già)
# 4. mostra le modifiche e chiede conferma
# 5. commit, tag vX.Y.Z e push: il tag avvia la GitHub Action che pubblica la release con lo zip
set -euo pipefail

cd "$(dirname "$0")/.."

VERSION="${1:-}"
MESSAGE="${2:-}"
DRY_RUN=0
for arg in "$@"; do
	[ "$arg" = "--dry-run" ] && DRY_RUN=1
done
[ "$MESSAGE" = "--dry-run" ] && MESSAGE=""

die() { echo "Errore: $*" >&2; exit 1; }

# --- Controlli -----------------------------------------------------------------
[[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] || die "indica la versione nel formato X.Y.Z, es. bin/release.sh 1.0.1 \"Descrizione\""

CURRENT=$(grep -m1 -E '^\s*\*\s*Version:' gb-popup.php | awk '{print $NF}')
[ "$VERSION" != "$CURRENT" ] || die "la versione attuale è già $CURRENT"

# La nuova versione deve essere maggiore di quella attuale
HIGHER=$(printf '%s\n%s\n' "$CURRENT" "$VERSION" | sort -t. -k1,1n -k2,2n -k3,3n | tail -1)
[ "$HIGHER" = "$VERSION" ] || die "$VERSION è minore della versione attuale $CURRENT"

[ "$(git branch --show-current)" = "main" ] || die "passa al branch main prima di pubblicare"
[ -z "$(git status --porcelain)" ] || die "ci sono modifiche non salvate in un commit (git status)"

git fetch --quiet --tags origin
[ "$(git rev-parse HEAD)" = "$(git rev-parse origin/main)" ] || die "il branch main non è allineato a GitHub (git pull / git push)"
! git rev-parse -q --verify "refs/tags/v$VERSION" >/dev/null || die "il tag v$VERSION esiste già"

if ! grep -q "^= $VERSION =" readme.txt; then
	[ -n "$MESSAGE" ] || die "aggiungi una descrizione: bin/release.sh $VERSION \"Cosa cambia\" (oppure scrivi la voce \"= $VERSION =\" nel changelog di readme.txt)"
fi

# --- Modifiche -----------------------------------------------------------------
perl -pi -e "s/^(\s*\*\s*Version:\s*).*/\${1}$VERSION/" gb-popup.php
perl -pi -e "s/define\( 'GB_POPUP_VERSION', '[^']*' \)/define( 'GB_POPUP_VERSION', '$VERSION' )/" gb-popup.php
perl -pi -e "s/^Stable tag:.*/Stable tag: $VERSION/" readme.txt

if ! grep -q "^= $VERSION =" readme.txt; then
	MESSAGE="$MESSAGE" VERSION="$VERSION" perl -0pi -e 's/(== Changelog ==\n)/$1= $ENV{VERSION} =\n* $ENV{MESSAGE}\n\n/' readme.txt
fi

# Verifica: le tre versioni devono corrispondere
grep -qE "^\s*\*\s*Version:\s*$VERSION$" gb-popup.php || die "versione non aggiornata nell'intestazione di gb-popup.php"
grep -q "define( 'GB_POPUP_VERSION', '$VERSION' )" gb-popup.php || die "GB_POPUP_VERSION non aggiornata"
grep -q "^Stable tag: $VERSION$" readme.txt || die "Stable tag non aggiornato"

echo
git --no-pager diff --color
echo

if [ "$DRY_RUN" = "1" ]; then
	git checkout -- gb-popup.php readme.txt
	echo "Prova (--dry-run): modifiche annullate, nessun commit."
	exit 0
fi

read -r -p "Pubblico la versione $VERSION (attuale $CURRENT)? [s/N] " ANSWER
if [[ ! "$ANSWER" =~ ^[sSyY]$ ]]; then
	git checkout -- gb-popup.php readme.txt
	echo "Annullato: modifiche ripristinate."
	exit 1
fi

# --- Pubblicazione -------------------------------------------------------------
git add gb-popup.php readme.txt
git commit --quiet -m "Versione $VERSION${MESSAGE:+: $MESSAGE}"
git tag -a "v$VERSION" -m "GB Popup $VERSION"
git push --quiet origin main
git push --quiet origin "v$VERSION"

REPO=$(git remote get-url origin | sed -E 's#(git@github.com:|https://github.com/)##; s#\.git$##')
echo
echo "Versione $VERSION pubblicata."
echo "Avanzamento della release: https://github.com/$REPO/actions"
echo "Release:                   https://github.com/$REPO/releases/tag/v$VERSION"
