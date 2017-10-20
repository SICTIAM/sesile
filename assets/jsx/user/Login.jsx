import React, { Component } from "react";
import { render } from "react-dom";

import Form from "react-jsonschema-form";

const schema = {
    title: "Connexion",
    type: "object",
    required: ["login", "password"],
    properties: {
        login: {type: "string", title: "E-mail"},
        password: {type: "string", format: "password", title: "Mot de passe"}
    }
};

const log = (type) => console.log.bind(console, type);

render((
    <Form schema={schema}
          onChange={log("changed")}
          onSubmit={log("submitted")}
          onError={log("errors")} />
), document.getElementById("app"));