## Specification

This library implements parts of FinTS V3.0, which is specified [here](https://www.hbci-zka.de/spec/3_0.htm).
Before version 3, the standard was called HBCI.
Today, HBCI still refers to the security part of the specification.
The specification is split into several documents:

* [Hauptdokument] Unimportant index document that gives an overview of the other documents.
* [Formals] Wire format and basic protocol (dialog, BPD, UPD).
* [Rückmeldungen] Directory of response codes that the bank can send.
* [Security HBCI] Protocol for encryption and cryptographic signatures.
* [Security PIN/TAN] Protocol for PIN/TAN authentication, based on the HBCI security protocol, but without actual encryption/signatures.
* [Messages Geschäftsvorfälle] Various business transactions (account statements, wire transfers, etc.).
* [Messages Finanzdatenformate] Wire formats other than the FinTS format itself (e.g. DTAUS and MT 940) that are used for certain transactions.

[Hauptdokument]: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01hc3Rlcl8yMDE4LTExLTI5LnBkZiIsInBhZ2UiOjEyN30.GC71gi5buUzoM0cYUoBFZ8_8PErGnNcqlNfzhSVJF74/FinTS_3.0_Master_2018-11-29.pdf
[Formals]: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
[Rückmeldungen]: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL0ZpblRTX1J1ZWNrbWVsZHVuZ3Njb2Rlc18yMDI1LTA4LTE5X0ZWLnBkZiIsInBhZ2UiOjEyN30.V9_N0iZSIveDYPTedp13vkC3u8xQ918Kh-ZDmE13e4E/FinTS_Rueckmeldungscodes_2025-08-19_FV.pdf
[Security HBCI]: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX0hCQ0lfUmVsXzIwMjQtMDYtMTFfZmluYWxfdmVyc2lvbi5wZGYiLCJwYWdlIjoxMjd9.HKqFIKBMLQVfvQfQFpgjJ9U93yv4mM3Now8IMdEIORY/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_2024-06-11_final_version.pdf
[Security PIN/TAN]: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL0ZpblRTX1J1ZWNrbWVsZHVuZ3Njb2Rlc18yMDI1LTA4LTE5X0ZWLnBkZiIsInBhZ2UiOjEyN30.V9_N0iZSIveDYPTedp13vkC3u8xQ918Kh-ZDmE13e4E/FinTS_Rueckmeldungscodes_2025-08-19_FV.pdf
[Messages Geschäftsvorfälle]: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
[Messages Finanzdatenformate]: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjQvRmluVFNfNC4xX01lc3NhZ2VzX0ZpbmFuemRhdGVuZm9ybWF0ZV8yMDE0LTAxLTIwLUZWLnBkZiIsInBhZ2UiOjEyN30.y4aWITiNK6G4loD-5rjr5ZydOqJ_zXW_mykqMKW2ieg/FinTS_4.1_Messages_Finanzdatenformate_2014-01-20-FV.pdf

Note that there is also an [archive](https://www.hbci-zka.de/spec/spec_archiv.htm) with older versions of the HBCI specification. 


## High-level concepts and structure of the library

A **message** (implemented in `Fhp\Protocol\Message`) is a series of **segments** (implemented in `Fhp\Segment\...`),
which themselves consist of **data elements (DEs)** and **data element groups (DEGs)**.
Each segment starts with an identifier to declare its type. Example:
```
HNHBK:1:3+000000000079+300+dialogID+2'HKEND:2:1+dialogID'HNHBS:3:1+2'
```
This message contains three segments.
The middle segment is `HKEND` version `1` at position `2`. It only contains one data element with value `dialogId`.
The first segment is `HNHBK`. It contains four data elements `000000000079`, ..., `2`.

The **syntactical representation (wire format)** (see [Formals]) used when transmitting/storing messages and segments is
described below and implemented in `Fhp\Syntax`.

The **protocol** specifies which messages a client can send (requests) and how the server can respond to them (responses).
There is exactly one response per request, the client needs to wait for the response before sending another request.
While FinTs is specified independently of the underlying transport layer, all banks use TLS 3.0 in practice, so it's
very similar to HTTPS and the server addresses are given as `https://` URIs as well.

The most basic protocol is the **dialog** (see [Formals], implemented in `Fhp\Protocol\DialogInitialization` and `FinTs`),
which combines elements of a handshake (as in TCP/TLS) and a session (i.e. authentication through cookies/tokens).
Before sending any other messages, the client must initialize a dialog.
Most dialogs require authentication, so the initialization is comparable to a login.

While all of the above is part of the FinTS infrastructure, the **business transactions** (the part of the protocol that is
ultimately useful for users) are specified separately in [Messages Geschäftsvorfälle] (implemented in `Fhp\Action\...`).
In addition to these, bank can specify their own actions (would be implemented in separate dependent libraries, but
currently there are none).

Separately from the FinTS specification, this library implements a **user-facing API** in `Fhp\Model` (in addition to
the `FinTs` class and the `Fhp\Action` namespace) to provide a simpler and more stable interface over time.

NOTE: The PHP namespaces `DataElementGroups`, `Dialog` and `Response` (and their contents) are deprecated.


## Segment/DEG schema

### Segment classes
In this library, segments are implemented in classes named like `HKTANv6` that inherit from `BaseSegment`.
Each of these classes is mapped 1:1 from the corresponding chapter of the specification document.
The class name consists of the segment identifier (e.g. `HKTAN`) and the version (just an integer prefixed by `v`).

The segment identifier itself has two parts:
In `HXyyy`, the `H` is constant (for "HBCI"), `yyy` is an abbreviation of the name of the respective functionality that
the segment provides, and `X` is one of `K`, `I` or `N` depending on whether it is a request segment sent by the client
("Kunde" = customer), a response segment sent by the server ("(Kredit-)Institut" = bank) or both ("Nachricht" = message).
The `HIyyyS` segments suffixed by `S` contain meta information (called "parameters") that describe what constitues a
valid `HKyyy` request.

The class `HXyyyvN` that implements version `N` of a particular segment type `yyy` can either be placed in the PHP
namespace `Fhp\Segment\HXyyy` (to group versions together, used especially when `X`=`N`) or `Fhp\Segment\yyy` (to group
corresponding request and reponse segments together).

### DEG classes
Analogously to segments, data element groups (DEGs) are mapped to classes that inherit from `BaseDeg`.
The class name is the title of the sub-section that specifies the DEG structure in the specification document.
The version suffix (`VN` with capital `V`) is optional for DEGs.
The naming does not have to be as strictly deterministic as with segments because DEGs are referenced explicitly in
code from the respective segments that contain them.

### Segment/DEG interfaces
Especially when there are multiple supported versions of the same segment/DEG type (e.g. `HKKAZv6` and `HKKAZv7`), it
makes sense to also have a common `interface HKKAZ` implemented by all versions that defines getters for the common
fields, so that business logic code can access all versions transparently.

### Member fields / data elements

As an example, consider the `HIUPDv6` class that implements the "Kontoinformation" segment from page 88 (PDF page 96)
from the [Formals] document.

Within each segment/DEG class, the elements defined in the specification are translated one by one to class fields.
It is important that the fields occur in the *same order* as in the specification and that no fields are left out, because
the absolute index/position of each field in the segment class must map to its index in HBCI's wire format.
Segment classes may inherit from other segment classes, in which case the fields of the parent class come first.
The name of the field/element is taken directly from the specification, so it is usually in German.
Special characters like Umlauts are replaced with their expanded versions and the whole name is transformed to camel case.

The element types (specified in [Formals] section B.4) are mapped to PHP types as follows:
- `jn` (yes/no) becomes `bool`.
- `num` (numerical) and `dig` (single digit) become `int`.
- `float` and `wrt` (amounts) become `float`.
- `an` (alpha-numerical), `txt` (text), `id` (identifiers), `ctr` (country codes, despite being numerical) and `cur`
  (currency codes) become `string` and the maximum length is documented in phpDoc.
- `code` (enum) is resolved to the type of the underlying value type (usually `string` or `int`) and the allowed values
  are documented in phpDoc.
- `bin` uses the `Fhp\Syntax\Bin` class and the maximum length is documented in phpDoc.
- `dat` and `tim` are currently mapped to `string`, but could get their own class in `Fhp\DataTypes` in future.
- Any data element group is implemented as a separate DEG class (see above), so that the PHP field can reference that
  class name as its type.

The "Status" of each element is mapped as follows: 
- Mandatory fields ("M" in the specification) use the plain type as described above.
- Optional fields ("O") append `|null`.
- Conditional fields ("C") are resolved as much as possible given the context of the segment and this library in general
(often only one of the conditions is always satisfied, so it is clear whether the field is mandatory, optional or
disallowed whenever it is used in this library), and otherwise mapped to `|null` as well.

The "Anzahl" (cardinality) is mapped as follows:
- If only `1` is allowed, use the plain (possibly nullable) type as described above.
- Otherwise append `[]` to the type and add a `@Max(N)` annotation for the maximum number. E.g. a `jn` field that can be
  repeated at most 20 times becomes `@var int[] @Max(20)`.
- If `0` is within the allowed range, add `|null`, e.g. `@var int[]|null @Max(20)`.

Any other information that is given in the specification like maximum length, restrictions on when the field can be
used or not, and the guidelines ("Belegungsrichtlinien"), are added to the phpDoc in English (translated) if useful.


## Wire format

Segments are terminated (and thus also delimited) by `'`.
They contain a series of DEs and DEGs, delimited by `:` and `+`. 
Note that these delimiters aren't very consistent (see below).

The values essentially form an ordered tree, where the leaves are data elements (similar to scalar values), inner nodes
are data element groups (similar to compound values) and the roots are segments.
The entries of a segment (i.e. the second level of the tree), which can be a mix of DEs and DEGs, are delimited by `+`.
The entries of any lower-level inner node are delimited by `:`.
Such a tree can be arbitrarily deep, which leads to ambiguities.
For instance, the wire format of a DEG `X` that contains nothing but a nested DEG `Y` is indistinguishable from the wire
format of `Y` alone.
With repeated and optional elements, there are even more ambiguities, but the specification requires "empty" elements to
clarify in such situations.  

While the wire format's syntax can be recognized and parsed without knowing what the contents mean (similar to JSON or
XML), the FinTs wire format is not self-describing on the semantic level, so the segment definitions are needed to
understand the contents (similar to protocol buffers and other binary serialization formats).
However, each segment begins with a `Segmentkopf` as its first entry, which declares the type of the segment and allows
picking the right segment definition/schema for parsing.

### Prettifier scripts

For debugging purposes, it can be useful to render serialized messages/segments in a more readable (self-describing)
format, specifically with the field names inlined.
The `prettify_message.php` and `prettify_segment.php` scripts do exactly that, given the wire format on stdin.


## PSD2 PIN/TAN authentication

The PIN/TAN specification piggy-backs on the HBCI encryption/signature specification that was originally intended for
cryptographically secure communication based on private keys contained in physical tokens like HBCI chip cards.
Nowadays, the surrounding transport layer (TLS) already provides the cryptographic security based on the public-key
infrastructure of the WWW, so that proper encryption and signatures are not necessary anymore on the FinTS level.
Nevertheless, all messages need to be wrapped in the respective envelope (implemented in `Fhp\Protocol\Message`).
This envelope also carries PIN and TAN whenever they need to be sent.

In addition to the envelope, the `HKTAN` family of segments are used to communicate whether a TAN is needed, what it
needs to look like, what it is for when it is being sent, and whether it was accepted on the bank side.
`HKTAN` version 6 is equivalent to PSD2.

In practice, with PSD2 for the first time, several German banks started asking for a TAN even upon login, which can
sometimes lead to problems depending on how they implement the protocol, given that `HKTAN` was designed to authenticate
business transactions *within* a dialog, but is now used to *initialize* a dialog as well.


### Tests

Very few banks provide a sandbox environment with fake accounts for testing purposes.
So most developers manual tests against their real bank accounts.
To minify the impact on these accounts (undesired transactions, account locks due to failed login attempts), automated
integration testing (against fake backends) has proven very useful (in addition to the usual regression-catching
benefits of those tests).

The `CLILogger` class can be used to record requests/responses during a (manually scripted) dialog with the bank.
The recorded messages can then be filled into a `PhpUnit` test.
When used through `FinTs::setLogger()`, the logger already replaces the most important sensitive values
(username, PIN, ...) with placeholders.
But the recorded segments regularly still contain personal information (e.g. IBANs and names of other parties involved
in transactions, descriptions of transactions, etc.).
In order to remove this information, it is advisable not to change the overall length (i.e. using the `Ins` mode in the
code editor to overwrite it with some dummy data), because the wire format hard-codes the length of subsequent data in
various places and simply removing it would result in parsing failures.
For examples, see `Tests\Fhp\Integration\...`.
 
