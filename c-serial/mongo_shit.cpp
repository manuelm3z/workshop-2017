mongocxx::cursor cursor = database_["Restreams"].find(document{} << "_id" <<
                                                      bsoncxx::oid(ids[i]) << finalize);
for (bsoncxx::document::view doc : cursor) {

    bsoncxx::document::element estado_field{doc["Estado"]};
    string estado = estado_field.get_utf8().value.to_string();

    if (estado == "1") {
        bsoncxx::document::element id_field{doc["_id"]};
        cerr << id_field.get_oid().value.to_string() << endl;
    }
}


void read() {
    mongocxx::client conn{mongocxx::uri{"mongodb://" + mongo_addr + ":27017"}};
    auto collection = conn["vCenterWeb"];

    bsoncxx::document::value entry = document{}
        << "Usuario" << userinfo.username
        << "FechaHora"<< bsoncxx::types::b_date{std::chrono::milliseconds{QDateTime::currentMSecsSinceEpoch()}}
        << finalize;
    try{
        collection["Inactividades"].insert_one(std::move(entry));
        ok = true;
        log("Se registra que el operario esta inactivo");

    } catch (const std::exception &exc) {
            string error =" error: ";
            log( (string)exc.what() + error + (string)exc.get_message());
            ok = false;
    }

}
