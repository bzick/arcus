<?php

namespace Arcus\Kits;


class MimeTypeKit {

    const DEFAULT_MIME_TYPE = "application/octet-stream";

    public static $types = [
        'application' => [
            "atom+xml" => "Atom",
            "EDI-X12" => "EDI X12", // RFC 1767
            "EDIFAC" => "EDI EDIFACT", // RFC 1767
            "json" => "JavaScript Object Notation JSON", // RFC 4627
            "javascript" => "JavaScript", // RFC 4329
            "octet-stream" => "Binary data", // RFC 2046
            "ogg" => "Ogg", // RFC 5334
            "pdf" => "Portable Document Format, PDF", // RFC 3778
            "postscript" => "PostScript", // RFC 2046
            "soap+xml" => "SOAP", // RFC 3902
            "x-woff" => "Web Open Font Format",
            "xhtml+xml" => "XHTML", // RFC 3236
            "xml-dtd" => "DTD", // RFC 3023
            "xop+xml" => "OP",
            "zip" => "ZIP",
            "x-gzip" => "Gzip",
            "x-bittorrent" => "BitTorrent",
            "x-tex " => "TeX",
            "vnd.debian.binary-package" => "deb (file format), a software package format used by the Debian project",
            "vnd.oasis.opendocument.text" => "OpenDocument Text",
            "vnd.oasis.opendocument.spreadsheet" => "OpenDocument Spreadsheet",
            "vnd.oasis.opendocument.presentation" => "OpenDocument Presentation",
            "vnd.oasis.opendocument.graphics" => "OpenDocument Graphics",
            "vnd.ms-excel" => "Microsoft Excel files",
            "vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "Microsoft Excel 2007 files",
            "vnd.ms-powerpoint" => "Microsoft Powerpoint files",
            "vnd.openxmlformats-officedocument.presentationml.presentation" => "Microsoft Powerpoint 2007 files",
            "vnd.openxmlformats-officedocument.wordprocessingml.document" => "Microsoft Word 2007 files",
            "vnd.mozilla.xul+xml" => "Mozilla XUL files",
            "vnd.google-earth.kml+xml" => "KML files (e.g. for Google Earth)",
            "vnd.google-earth.kmz" => "KMZ files (e.g. for Google Earth)",
            "vnd.android.package-archive" => "For download apk files.",
            "vnd.ms-xpsdocument" => "XPS document",
        ],
        'audio' => [
            "basic" =>"mulaw audio, 8 kHz, 1 channel", // RFC 2046
            "L24" => "24bit Linear PCM audio, 8-48 kHz, 1-N channels", // RFC 3190
            "mp4" => "MP4",
            "mpeg" => "MP3 or other MPEG audio", // RFC 3003
            "ogg" => "Vorbis, Opus, Speex, Flac and other audio in an Ogg container", // RFC 5334
            "vorbis" => "Vorbis", // RFC 5215
            "x-ms-wma" => "Windows Media Audio",
            "x-ms-wax" =>" Windows Media Audio redirect",
            "vnd.rn-realaudio" => "RealAudio",
            "vnd.wave" => "WA", // RFC 2361
            "webm" => "WebM",
        ],
        'image' => [
            "gif" => "GIF image ", // RFC 2045 and RFC 2046
            "jpeg" => "JPEG JFIF image", // RFC 2045 and RFC 2046
            "pjpeg" => "JPEG JFIF image; Associated with Internet Explorer; Listed in ms775147(v=vs.85) - Progressive JPEG, initiated before global browser support for progressive JPEGs (Microsoft and Firefox).",
            "png" => "Portable Network Graphics", // RFC 2083
            "bmp" => "BMP file format;",
            "svg+xml" => "SVG vector image",
            "tiff" => "TIF image",
            "vnd.djvu" => "DjVu image and multipage document format",
            "x-icon" => "Icon image"
        ],
        'multipart' => [
            "mixed" => "MIME Email", //  RFC 2045 and RFC 2046
            "alternative" => "MIME Email", //  RFC 2045 and RFC 2046
            "related" => "MIME Email", //  RFC 2387 and used by MHTML (HTML mail)
            "form-data" => "MIME Webform", //  RFC 2388
            "signed" => "Signed", // RFC 1847
            "encrypted" => "Encrypted ", // RFC 1847
        ],
        'text' => [
            "cmd" => "commands; subtype resident in Gecko browsers like Firefox 3.5",
            "css" => "Cascading Style Sheets", // RFC 2318
            "csv" => "Comma-separated values", // RFC 4180
            "html" => "HTML", // RFC 2854
            "javascript" => "JavaScript",
            "plain" => "Textual data", // RFC 2046 and RFC 3676
            "rtf" => "RTF",
            "vcard" => "vCard (contact information)", // RFC 6350
            "vnd.a" => "The A language framework",
            "vnd.abc" => "ABC music notation",
            "xml" => "Extensible Markup Language", // RFC 3023
        ]
    ];

    public static $file_type = [
        // application
        "zip" => "application/zip",
        "pdf" => "application/pdf",
        "json" => "application/json",
        "gz" => "application/x-gzip",
        // image
        "gif" => "image/gif",
        "jpeg" => "image/jpeg",
        "jpg" => "image/jpeg",
        "png" => "image/png",
        "bmp" => "image/bmp",
        "ico" => "image/x-icon",
        // text
        "css" => "text/css",
        "csv" => "text/csv",
        "html" => "text/html",
        "htm" => "text/html",
        "js" => "text/javascript",
        "txt" => "text/plain",
        "rtf" => "text/rtf",
        "xml" => "text/xml",
    ];

    /**
     * @param string $ext
     * @return string
     */
    public static function getMimeByExt(string $ext) : string {
        return isset(self::$file_type[$ext]) ? self::$file_type[$ext] : self::DEFAULT_MIME_TYPE;
    }

    /**
     * @param string $basename
     * @return string
     */
    public static function getMimeByFile(string $basename) : string {
        $ext = substr($basename, strrpos($basename, ".") + 1);
        return self::getMimeByExt($ext);
    }
}