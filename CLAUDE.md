# SemanticResultFormats — Claude Code Guidelines

## After every commit of type `feat`, `fix`, `test`, `refactor`, or `docs`

Add an entry to the `[Unreleased]` section (the topmost `## SRF x.y.z` block) of **`RELEASE-NOTES.md`**.

- Place it under the appropriate subsection (`New Features and Enhancements`, `Bug Fixes`, `Tests`, etc.).
- Format: `* <What changed> ([<issue/PR>](<url>)) (by [gesinn.it](https://gesinn.it))`
- If no issue/PR exists yet, omit the link.
- Do this **before** committing, or as a follow-up commit immediately after.
