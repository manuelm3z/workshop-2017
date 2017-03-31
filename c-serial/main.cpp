#include <cstdio>
#include <cstring>
#include <unistd.h>
#include <fcntl.h>
#include <errno.h>
#include <termios.h>
#include <thread>
#include <iostream>

#include <bsoncxx/builder/basic/array.hpp>
#include <bsoncxx/builder/basic/sub_array.hpp>
#include <bsoncxx/builder/stream/document.hpp>
#include <bsoncxx/json.hpp>

//MONGO
#include <mongocxx/client.hpp>
#include <mongocxx/instance.hpp>
#include <mongocxx/options/find.hpp>
#include <mongocxx/uri.hpp>

using bsoncxx::builder::stream::document;
using bsoncxx::builder::stream::open_document;
using bsoncxx::builder::stream::close_document;
using bsoncxx::builder::stream::open_array;
using bsoncxx::builder::stream::close_array;
using bsoncxx::builder::stream::finalize;

using namespace std;
using namespace std::chrono;

int main() {

    try {
        mongocxx::client conn{mongocxx::uri{"mongodb://192.168.0.10:27017"}};

    mongocxx::database db = conn["vOne"];

    mongocxx::collection coll = db["Remotas"];

    mongocxx::cursor cursor = coll.find(document{} <<finalize);

    for (bsoncxx::document::view doc : cursor) {
        bsoncxx::document::element estado_field{doc["Estado"]};

        string estado = estado_field.get_utf8().value.to_string();

        if (estado == "1") {
            bsoncxx::document::element id_field{doc["_id"]};

            string id = id_field.get_oid().value.to_string();

            fprintf(stdout, "%s\n", id);
        }
    }

} catch (std::exception &e) {
        cerr << e.what() << endl;
    }
    /*int serialfd = open("/dev/ttyS0", O_RDWR | O_NOCTTY);

    if (serialfd == -1)
        perror("Error opening the serial port");
    else
        fcntl(serialfd, F_SETFL, 0);

    fprintf(stdout, "Device is open, attempting read \n");

    fcntl(serialfd, F_SETFL, 0);

    while (true) {
        unsigned char buf[2] = {193, 0};

        write(serialfd, buf, 2);
        
        this_thread::sleep_for(seconds{1});
        
        read(serialfd, buf, 2);

        fprintf(stderr, "Buffer: %d %d", int(buf[0]), int(buf[1]));
        //close(serialfd);
    }*/
    return 0;
}


/* g++ main.cpp -ljsoncpp -lbsoncxx -lmongocxx -std=c++1z -I/usr/local/include/bsoncxx/json -I/usr/local/include/mongocxx -o main*/