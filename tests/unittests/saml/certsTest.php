<?php
use phpseclib3\File\X509;
use phpseclib3\Crypt\RSA;
use Jelix\Saml\Configuration;

class certsTest extends \Jelix\UnitTests\UnitTestCase {

    /**
     * Génère un certificat X509 signé, avec des bornes de validité données.
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return string PEM du certificat
     */
    protected function generateCert(\DateTimeInterface $start, \DateTimeInterface $end): string
    {
        // clé privée/publique
        $pk = RSA::createKey(2048);
        $privKey = $pk->withPadding(RSA::ENCRYPTION_PKCS1 | RSA::SIGNATURE_PKCS1);
        $pubKey = $privKey->getPublicKey();

        // Sujet
        $subject = new X509();
        $subject->setDN('C=FR, ST=IDF, L=Paris, O=ACME, OU=IT');
        $subject->setPublicKey($pubKey);
        $subject->setDomain('example.org');

        // Émetteur (self-signed)
        $issuer = new X509();
        $issuer->setPrivateKey($privKey);
        $issuer->setDN($subject->getDN());

        // Construction et signature
        $x509 = new X509();
        $x509->setStartDate((new \DateTimeImmutable('@'.$start->getTimestamp()))->setTimezone($start->getTimezone()));
        $x509->setEndDate((new \DateTimeImmutable('@'.$end->getTimestamp()))->setTimezone($end->getTimezone()));
        $signedCert = $x509->sign($issuer, $subject);

        return $x509->saveX509($signedCert);
    }

    protected function getNowTZ(): \DateTimeZone
    {
        $cfg = \jApp::config();
        $tz = ($cfg && isset($cfg->timeZone) && $cfg->timeZone) ? $cfg->timeZone : 'UTC';
        return new \DateTimeZone($tz);
    }

    public function testEmptyCertificate()
    {
        $conf = new Configuration(false, \jApp::config());
        [$status, $info] = $conf->checkCertificate('');
        $this->assertEquals(Configuration::CERT_UNKNOWN, $status);
        $this->assertEquals('', $info);
    }

    public function testBadFormatCertificate()
    {
        $conf = new Configuration(false, \jApp::config());
        [$status, $info] = $conf->checkCertificate("-----BEGIN CERTIFICATE-----\nINVALID\n-----END CERTIFICATE-----\n");
        $this->assertEquals(Configuration::CERT_BAD_FORMAT, $status);
    }

    public function testNotYetValidCertificate()
    {
        $tz = $this->getNowTZ();
        $now = new \DateTimeImmutable('now', $tz);
        $start = $now->modify('+2 days');
        $end = $now->modify('+32 days');
        $cert = $this->generateCert($start, $end);

        $conf = new Configuration(false, \jApp::config());
        [$status, $info] = $conf->checkCertificate($cert);
        $this->assertEquals(Configuration::CERT_NOT_YET_VALID, $status);
        // info = date notBefore formatée
        $this->assertIsString($info);
    }

    public function testExpiredCertificate()
    {
        $tz = $this->getNowTZ();
        $now = new \DateTimeImmutable('now', $tz);
        $start = $now->modify('-40 days');
        $end = $now->modify('-1 day');
        $cert = $this->generateCert($start, $end);

        $conf = new Configuration(false, \jApp::config());
        [$status, $info] = $conf->checkCertificate($cert);
        $this->assertEquals(Configuration::CERT_EXPIRED, $status);
        $this->assertIsString($info);
    }

    public function testAlmostExpiredCertificate()
    {
        $tz = $this->getNowTZ();
        $now = new \DateTimeImmutable('now', $tz);
        $start = $now->modify('-1 day');
        $end = $now->modify('+10 days'); // < 30 jours
        $cert = $this->generateCert($start, $end);

        $conf = new Configuration(false, \jApp::config());
        [$status, $info] = $conf->checkCertificate($cert);
        $this->assertEquals(Configuration::CERT_ALMOST_EXPIRED, $status);
        // info = nb jours restants (entier)
        $this->assertIsInt($info);
        $this->assertLessThan(30, $info);
        $this->assertGreaterThanOrEqual(0, $info);
    }

    public function testValidCertificate()
    {
        $tz = $this->getNowTZ();
        $now = new \DateTimeImmutable('now', $tz);
        $start = $now->modify('-1 day');
        $end = $now->modify('+40 days'); // >= 30 jours
        $cert = $this->generateCert($start, $end);

        $conf = new Configuration(false, \jApp::config());
        [$status, $info] = $conf->checkCertificate($cert);
        $this->assertEquals(Configuration::CERT_VALID, $status);
        $this->assertIsString($info); // date notAfter formatée
    }
}
