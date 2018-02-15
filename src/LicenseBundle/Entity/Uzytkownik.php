<?php

namespace LicenseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Uzytkownik
 *
 * @ORM\Table(name="uzytkownik")
 * @ORM\Entity(repositoryClass="LicenseBundle\Repository\UzytkownikRepository")
 */
class Uzytkownik implements UserInterface, \Serializable {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\OneToMany(targetEntity="RezerwacjaI", mappedBy="idUzytkownik")
     * @ORM\OneToMany(targetEntity="RezerwacjaII", mappedBy="idUzytkownik")
     * @ORM\OneToMany(targetEntity="Wypozyczenie", mappedBy="idUzytkownik")

     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Imie", type="string", length=255)

     */
    private $imie;

    /**
     * @var string
     *
     * @ORM\Column(name="Nazwisko", type="string", length=255)

     */
    private $nazwisko;

    /**
     * @var string
     *
     * @ORM\Column(name="Login", type="string", length=255, unique=true)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="Haslo", type="string", length=255, unique=false)
     */
    private $haslo;

    /**
     * @var string
     *
     * @ORM\Column(name="Mail", type="string", length=255, unique=false)
     */
    private $mail;

    /**
     * @var array<string>
     *
     * @ORM\Column(name="Role", type="simple_array", nullable=true)
     */
    private $role;

    /**
     * @var bool
     *
     * @ORM\Column(name="Aktywny", type="boolean")
     */
    private $aktywny;

    /**
     * @var bool
     *
     * @ORM\Column(name="Kierownik", type="boolean", nullable=true)
     */
    private $kierownik;

    /**
     * @var string
     *
     * @ORM\Column(name="Uuid", type="string", length=255, unique=true, nullable=true)
     */
    private $uuid;

    public function getSalt() {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword() {
        return $this->haslo;
    }

    public function getRoles() {
        return $this->role;
        //return array('ROLE_ADMIN');
    }

    public function getUsername() {
        return $this->login;
    }

    public function eraseCredentials() {
        
    }

    /** @see \Serializable::serialize() */
    public function serialize() {
        return serialize(array(
            $this->id,
            $this->login,
            $this->haslo,
                // see section on salt below
                // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized) {
        list (
                $this->id,
                $this->login,
                $this->haslo,
                // see section on salt below
                // $this->salt
                ) = unserialize($serialized);
    }

    public function __toString() {
        return $this->imie . ' ' . $this->nazwisko;
    }

    public function addRole($rola) {
        array_push($this->role, strtoupper($rola));
        $this->role = array_unique($this->role);
        return $this;
    }

    public function removeRole($rola) {
        if (in_array(strtoupper($rola), $this->role)) {
            $tmp = array_diff($this->role, array(strtoupper($rola)));
            $this->role = array_unique($tmp);
        }
        return $this;
    }

    public function hasRole($rola) {
        if (in_array(strtoupper($rola), $this->role)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set imie
     *
     * @param string $imie
     *
     * @return Uzytkownik
     */
    public function setImie($imie) {
        $this->imie = $imie;

        return $this;
    }

    /**
     * Get imie
     *
     * @return string
     */
    public function getImie() {
        return $this->imie;
    }

    /**
     * Set nazwisko
     *
     * @param string $nazwisko
     *
     * @return Uzytkownik
     */
    public function setNazwisko($nazwisko) {
        $this->nazwisko = $nazwisko;

        return $this;
    }

    /**
     * Get nazwisko
     *
     * @return string
     */
    public function getNazwisko() {
        return $this->nazwisko;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return Uzytkownik
     */
    public function setLogin($login) {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * Set haslo
     *
     * @param string $haslo
     *
     * @return Uzytkownik
     */
    public function setHaslo($haslo) {
        $this->haslo = $haslo;

        return $this;
    }

    /**
     * Get haslo
     *
     * @return string
     */
    public function getHaslo() {
        return $this->haslo;
    }

    /**
     * Set role
     *
     * @param array $role
     *
     * @return Uzytkownik
     */
    public function setRole($role) {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return array
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * Set mail
     *
     * @param string $mail
     *
     * @return Uzytkownik
     */
    public function setMail($mail) {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail() {
        return $this->mail;
    }

    /**
     * Set aktywny
     *
     * @param boolean $aktywny
     *
     * @return Uzytkownik
     */
    public function setAktywny($aktywny) {
        $this->aktywny = $aktywny;

        return $this;
    }

    /**
     * Get aktywny
     *
     * @return boolean
     */
    public function getAktywny() {
        return $this->aktywny;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return Uzytkownik
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid() {
        return $this->uuid;
    }


    /**
     * Set kierownik
     *
     * @param boolean $kierownik
     *
     * @return Uzytkownik
     */
    public function setKierownik($kierownik)
    {
        $this->kierownik = $kierownik;

        return $this;
    }

    /**
     * Get kierownik
     *
     * @return boolean
     */
    public function getKierownik()
    {
        return $this->kierownik;
    }
}
