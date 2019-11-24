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

[Hauptdokument]: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Master_2018-11-29.pdf
[Formals]: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
[Rückmeldungen]: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/FinTS_Rueckmeldungscodes_2019-07-22_final_version.pdf
[Security HBCI]: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
[Security PIN/TAN]: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
[Messages Geschäftsvorfälle]: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
[Messages Finanzdatenformate]: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Finanzdatenformate_2010-08-06_final_version.pdf

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


## Wire format

Segments are terminated (and thus also delimited) by `'`.
They contain a series of DEs and DEGs, delimited by `:` and `+`. 
Note that these delimiters aren't very consistent (see below).

The values essentially form a tree, where the leaves are data elements (similar to scalar values), inner nodes are data
element groups (similar to compound values) and the roots are segments.
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

The `SanitizingCLILogger` class can be used to record requests/responses during a (manually scripted) dialog with the
bank.
The recorded messages can then be filled into a `PhpUnit` test.
While the logger already replaces the most important sensitive values (username, PIN, ...), the recorded segments
regularly still contain personal information (e.g. IBANs and names of other parties involved in transactions,
descriptions of transactions, etc.).
In order to remove this information, it is advisable not to change the overall length (i.e. using the `Ins` mode in the
code editor to overwrite it with some dummy data), because the wire format hard-codes the length of subsequent data in
various places and simply removing it would result in parsing failures.
For examples, see `Tests\Fhp\Integration\...`.
 