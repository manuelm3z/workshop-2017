#include <iostream>
#include <unistd.h>
#include <fcntl.h>
#include <thread>

int main(int argc, char const *argv[])
{
	std::string tty = argv[1];

	std::string arg1 = argv[2];

	std::string arg2 = argv[3];

	int fd = open(tty.c_str(), O_RDWR | O_NOCTTY);

	unsigned char buf[2] = {std::stoi(arg1), std::stoi(arg2)};

	write(fd, buf, 2);

	std::this_thread::sleep_for(std::chrono::seconds{1});

	read(fd, buf, 2);

	std::cout << int(buf[0]) << ":" << int(buf[1]) << std::endl;

	return 0;
}