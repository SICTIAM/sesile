import React, { Component } from 'react'
import {Link} from "react-router-dom";

const CertificateValidity = ({ certificate, certificateRemainingDays, CertifRemain, NoCertif }) =>
    <div className="cell small-6 medium-6 large-6">
        {(certificate && certificateRemainingDays) ?
            <Link
                className="text-bold"
                style={{textDecoration:"underline"}}
                to="/utilisateur/certificat-electronique">
                    <span style={{margin: '5px'}}>
                        {CertifRemain}
                    </span>
            </Link> :
            <span style={{margin: '5px'}}>
                {NoCertif}
            </span>}
    </div>

export { CertificateValidity }