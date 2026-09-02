"""Module to generate prime numbers."""

def is_prime(n: int) -> bool:
    """Check if a number is prime."""
    if n < 2:
        return False
    if n in (2, 3):
        return True
    if n % 2 == 0 or n % 3 == 0:
        return False
    i = 5
    while i * i <= n:
        if n % i == 0 or n % (i + 2) == 0:
            return False
        i += 6
    return True


def get_first_n_primes(count: int) -> list[int]:
    """Return a list containing the first `count` prime numbers."""
    primes = []
    candidate = 2
    while len(primes) < count:
        if is_prime(candidate):
            primes.append(candidate)
        candidate += 1
    return primes


def main():
    """Print the first 100 prime numbers."""
    primes = get_first_n_primes(100)
    print("Les 100 premiers nombres premiers sont :")
    for index, prime in enumerate(primes, start=1):
        print(f"{index}: {prime}")


if __name__ == "__main__":
    main()
