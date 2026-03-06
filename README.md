# WP Update Helper

Prosty plugin WordPress do porównywania stanu pluginów, motywów, WordPressa i PHP przed i po aktualizacji.

## Po co?

Aktualizacje w WordPress mogą zmienić więcej niż się spodziewasz. Ten plugin pozwala zrobić snapshot środowiska **przed** aktualizacją i porównać go ze stanem **po** — żebyś od razu wiedział co dokładnie się zmieniło.

## Co zbiera?

- Lista wszystkich pluginów z wersjami (aktywne i nieaktywne)
- Lista wszystkich motywów z wersjami
- Wersja WordPress
- Wersja PHP

## Instalacja

1. Pobierz plik `wp-update-helper.php`
2. Wgraj do katalogu `/wp-content/plugins/szamaniwp-wp-update-helper/`
3. Aktywuj plugin w panelu WordPress → Pluginy

## Jak używać?

1. Przejdź do **Narzędzia → Szamaniwp Updates**
2. Kliknij **"Zapisz status PRZED"** — plugin zapisuje aktualny stan
3. Wykonaj aktualizacje (pluginy, motywy, WordPress)
4. Wróć do **Narzędzia → Szamaniwp Updates**
5. Kliknij **"Zapisz status PO"**
6. Gotowe — plugin wyświetla czytelne porównanie z zaznaczonymi zmianami:
   - Zielone tło = coś zostało dodane lub zaktualizowane (nowa wersja)
   - Czerwone tło = coś zostało usunięte lub obniżone

Po zakończeniu pracy kliknij **Reset**, żeby wyczyścić zapisane dane.

## Wymagania

- WordPress 5.0+
- PHP 7.4+
- Uprawnienia administratora (`manage_options`)

## Autor

Szamaniwp — [Szamaniwp.pl](https://Szamaniwp.pl)
