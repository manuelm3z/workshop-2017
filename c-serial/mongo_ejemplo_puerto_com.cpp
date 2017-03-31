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
#include <mongocxx/collection.hpp>

using bsoncxx::builder::stream::document;
using bsoncxx::builder::stream::open_document;
using bsoncxx::builder::stream::close_document;
using bsoncxx::builder::stream::open_array;
using bsoncxx::builder::stream::close_array;
using bsoncxx::builder::stream::finalize;

using namespace std;
using namespace std::chrono;

using uchar = unsigned char;



void check_event(uchar respuesta, uchar id_periferico,
                 mongocxx::collection &eventos, const std::string &object_id) {
    cerr << "Respuesta: " << (int)respuesta  << " " << (int)id_periferico << " " << "Object id " << object_id << endl;

    auto order = bsoncxx::builder::stream::document{} << "$natural" << -1
                                             << bsoncxx::builder::stream::finalize;

    auto opts = mongocxx::options::find{};
    opts.sort(order.view());

    auto doc = eventos.find_one(document{} << "Remota" << object_id << "Periferico"
                                           << to_string(id_periferico) << "Codigo"
                                           << to_string(respuesta) << finalize,
                                   opts);

    cerr << "Before Doc " << endl;
    if (doc) {
        cerr << "Codigo " << doc->view()["Codigo"].get_utf8().value.to_string() << endl;
        /*for (const auto &elem : doc->view()) {
            cerr << "Codigo " << elem["Codigo"].get_utf8().value.to_string() << endl;
            //elem["Codigo"] == respuesta;
        }*/
    }
    cerr << "After doc " << endl;
}

void check_devices(const std::string &tty, const bsoncxx::array::view &arr,
                   mongocxx::collection &eventos, const std::string &object_id) {
    std::string tty_completo = "/dev/" + tty;
    fprintf(stderr, "%s\n", tty_completo.c_str());
    int fd = open(tty_completo.c_str(), O_RDWR | O_NOCTTY);

    if (fd == -1) {
        perror("Error opening the serial port\n");
       // return;
    } else
        fcntl(fd, F_SETFL, 0);

    fprintf(stdout, "Device is open, attempting read \n");
    fcntl(fd, F_SETFL, 0);

    for (const auto &elem : arr) {
        uchar id = stoi(elem["Id"].get_utf8().value.to_string()), comando = 0;
        if (elem["Comando"]) {
            comando = stoi(elem["Comando"].get_utf8().value.to_string());
        }

        /* TODO: Hacer algo con los comandos?.. */
        unsigned char buf[2] = {192 + id, comando};
        write(fd, buf, 2);
        this_thread::sleep_for(seconds{1});
        read(fd, buf, 2);

        /* TODO: Guardar en mongo */
        check_event(buf[1], id, eventos, object_id);
        fprintf(stderr, "Buffer: %d %d\n", int(buf[0]), int(buf[1]));
    }

    close(fd);
}

int main() {
    mongocxx::client conn{mongocxx::uri{"mongodb://192.168.0.10:27017"}};
    mongocxx::database db = conn["vOne"];

    mongocxx::collection coll = db["Remotas"];
    mongocxx::collection eventos = db["Eventos"];
    while (true) {
        mongocxx::cursor cursor = coll.find(document{} << finalize);

        for (bsoncxx::document::view doc : cursor) {
            bsoncxx::document::element estado_field{doc["Estado"]};
            string estado = estado_field.get_utf8().value.to_string();
            string object_id = doc["_id"].get_oid().value.to_string();
            if (estado == "1") {
                if (doc["Serial"]) {
                    check_devices(doc["Serial"].get_utf8().value.to_string(),
                                  doc["Perifericos"].get_array(), eventos, object_id);
                } else if (doc["Ip"]) {
                    fprintf(stderr, "Error protocolo Ip no implementado\n");
                } else {
                    fprintf(stderr, "Error entrada invalida\n");
                }
            }
        }
    }

    return 0;
}


/* g++ main.cpp -ljsoncpp -lbsoncxx -lmongocxx -std=c++1z -I/usr/local/include/bsoncxx/json -I/usr/local/include/mongocxx -o main*/
