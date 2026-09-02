import unittest
from prime_numbers import is_prime, get_first_n_primes


class TestPrimeNumbers(unittest.TestCase):

    def test_is_prime(self):
        self.assertFalse(is_prime(-5))
        self.assertFalse(is_prime(0))
        self.assertFalse(is_prime(1))
        self.assertTrue(is_prime(2))
        self.assertTrue(is_prime(3))
        self.assertFalse(is_prime(4))
        self.assertTrue(is_prime(5))
        self.assertFalse(is_prime(9))
        self.assertTrue(is_prime(541))  # 100th prime number is 541

    def test_get_first_n_primes_count(self):
        primes = get_first_n_primes(100)
        self.assertEqual(len(primes), 100)

    def test_get_first_n_primes_values(self):
        primes = get_first_n_primes(10)
        expected = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29]
        self.assertEqual(primes, expected)

    def test_100th_prime(self):
        primes = get_first_n_primes(100)
        self.assertEqual(primes[-1], 541)


if __name__ == "__main__":
    unittest.main()
