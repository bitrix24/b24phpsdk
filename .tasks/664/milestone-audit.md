# Milestone 3.7.0 changelog audit

Audited on 2026-09-30 against origin/v3-dev `8be2874e` and milestone 21. All 30 closed items (18 merged PRs and 12 issues) map to release notes. PR #648 additionally covers #645 and #647. The delivery audit includes source ancestor checks for every merged PR.

| Item | Kind | Title | Release-note issue |
|---|---|---|---|
| #663 | Merged PR | Add Disk search and folder sharing wrappers | #659 |
| #662 | Merged PR | Add REST v3 user history services | #656 |
| #659 | Closed issue | Add missing legacy REST methods in disk scope | #659 |
| #657 | Merged PR | Complete REST v3 event-log validation and casting | #653 |
| #656 | Closed issue | Add REST v3 services for main.user.history.* | #656 |
| #655 | Merged PR | feat: expand legacy sonet_group coverage safely | #331 |
| #654 | Merged PR | feat: complete legacy timeman scope coverage | #642 |
| #653 | Closed issue | Complete main.eventlog.* implementation and validation for REST v3 | #653 |
| #652 | Merged PR | Add API coverage statistics to the maintainer release workflow | #650 |
| #651 | Merged PR | feat: expand legacy log scope coverage | #643 |
| #650 | Closed issue | Add API coverage statistics to the maintainer release workflow | #650 |
| #649 | Merged PR | docs: audit legacy imbot coverage and replacements | #270 |
| #648 | Merged PR | Add legacy Pull and callback services and fix scope coverage | #646 |
| #647 | Closed issue | Complete legacy pull_channel scope coverage in SDK v3 | #647 |
| #646 | Closed issue | Complete legacy call scope coverage in SDK v3 | #646 |
| #645 | Closed issue | Complete legacy pull scope coverage in SDK v3 | #645 |
| #643 | Closed issue | Complete legacy log scope coverage in SDK v3 | #643 |
| #642 | Closed issue | Complete legacy timeman scope coverage in SDK v3 | #642 |
| #639 | Merged PR | Fix OAuth credential leaks in Core exception handling | #552 |
| #638 | Merged PR | Fix legacy API coverage calculation on v3-dev | #637 |
| #637 | Closed issue | Fix legacy API coverage calculation on v3-dev | #637 |
| #633 | Merged PR | Feature/616 add batch to landing v3 | #616 |
| #617 | Merged PR | Feature/614 add batch to sale v3 | #614 |
| #612 | Merged PR | Feature/611 add batch to catalog v3 | #611 |
| #609 | Merged PR | Feature/608 add batch to booking v3 | #608 |
| #606 | Merged PR | Feature/605 add userfieldconfig v3 | #605 |
| #604 | Merged PR | Feature/603 add call.followup v3 | #603 |
| #601 | Merged PR | Feature/590 add catalog.vat v3 | #590 |
| #552 | Closed issue | [Bug in SDK]: Core::call() logs raw transport-exception message, leaking OAuth client_secret/refresh_token when the token-refresh request fails at the network level | #552 |
| #270 | Closed issue | Complete legacy imbot scope coverage in SDK v3 | #270 |

## Findings and disposition

- Consolidated duplicate Changed headings and moved new services/batch capabilities into Added; moved the IMBot documentation audit into Changed. Preserved detailed method lists, constructor changes and historical releases.
- Included the existing Deptrac workflow repair under release issue #664.
- #632 remains open; Core still changes the credential host before repeating a redirected call. This release does not fix that issue; keep the release PR draft pending resolution or explicit maintainer disposition.
- #663: successful live folder sharing was skipped without a configured recipient. Disk allowed-operation contracts remain unresolved.
- #639: historical full Core integration had portal quota and scope drift failures; focused Core calls passed.
- #654: Timeman mutation paths were not live-tested; some populated records were fixture-only.
- #655: invitation/role mutations were not sent to real users; four subject-method contracts remain unresolved.
- #648: successful telephone calls and OAuth push/event/watch delivery were not exercised.
- #649/#270: 28 deprecated legacy IMBot methods were mapped to replacements; nine public contracts remain unresolved. No legacy wrappers were added by that documentation audit.
- #651/#643: undocumented log.comment.delete is excluded.
- Closed issue status is not a claim of exhaustive live validation. See each merged PR for its original evidence and limitations.
