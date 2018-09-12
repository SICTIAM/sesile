import React, { Component } from 'react'

const CertificateValidity = ({ certificate, certificateRemainingDays, CertifRemain, NoCertif }) =>
    <div className="cell small-12 medium-12 large-12">
        {(certificate && certificateRemainingDays) ?
            <span style={{margin: '5px'}}>
                {CertifRemain}
            </span> :
            <span style={{margin: '5px'}}>
                {NoCertif}
            </span>}
    </div>

export { CertificateValidity }