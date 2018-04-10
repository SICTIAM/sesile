import React from 'react'
import {handleErrors} from "./Utils";
import History from "./History";
import {basicNotification} from "../_components/Notifications";

const refusClasseur = (that, url, id, motif = '', type = '') => {
    fetch(Routing.generate(url, {id}),
        {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                motif: motif
            }),
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(classeur => {
            type === 'list'
                ? that.listClasseurs(that.state.sort, that.state.order, that.state.limit, that.state.start, that.state.userId)
                : that.setState({classeur})
        })
}


const actionClasseur = (that, url, id, method = 'PUT', type = '') => {
    fetch(Routing.generate(url, {id}),
        {
            method: method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(classeur => {
            type === 'list'
                ? that.listClasseurs(that.state.sort, that.state.order, that.state.limit, that.state.start, that.state.userId)
                : that.setState({classeur})
        })
        .then(() => {
            if (method === 'PUT') {
                that.context._addNotification(basicNotification(
                    'success',
                    that.context.t('classeur.success.edit')))
            } else {
                that.context._addNotification(basicNotification(
                    'success',
                    that.context.t('classeur.success.delete')))
                History.push(`/classeurs/supprimes`)
            }
        })
        .catch(error => that.context._addNotification(basicNotification(
            'error',
            that.context.t('classeur.error.edit', {errorCode: error.status}),
            error.statusText)))
}

export { refusClasseur, actionClasseur }