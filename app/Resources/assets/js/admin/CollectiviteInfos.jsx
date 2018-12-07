import React, { Component } from 'react'
import { func, object, number, string, bool } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import InputValidation from '../_components/InputValidation'
import { InputFile, Avatar, Switch, Button, ButtonConfirm } from '../_components/Form'
import { AccordionItem } from '../_components/AdminUI'

class CollectiviteInfos extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    static propTypes = {
        id: number,
        nom: string.isRequired,
        siren: string,
        ozwilloId: string,
        image: string,
        active: bool.isRequired,
        delete_classeur_after: number.isRequired,
        handleChange: func.isRequired,
        putCollectivite: func.isRequired,
        editState: bool.isRequired
    }

    state = {isFormValid: false}

    validationRules = {
        delete_classeur_after: 'required|min:10|max:365'
    }

    customErrorMessages = {
        delete_classeur_after: {
            min: this.context.t('admin.collectivite.error.field_delay_min'),
            max: this.context.t('admin.collectivite.error.field_delay_max'),
            required: this.context.t('admin.collectivite.error.field_delay_required')
        }
    }

    updateAvatar = (file) => {
        if (file) {
            const data = new FormData()
            data.append('image', file)
            fetch(Routing.generate('sesile_main_collectiviteapi_uploadavatar', {id: this.props.id}), {
                credentials: 'same-origin',
                method: 'POST',
                body: data
            })
                .then(handleErrors)
                .then(response => response.json())
                .then(json => {
                        this.context._addNotification(basicNotification(
                            'success',
                            this.context.t('admin.collectivite.success_upload_avatar')))
                        this.props.handleChange('image', json.image)
                    }
                )
                .catch(error => this.context._addNotification(basicNotification(
                    'error',
                    this.context.t('admin.collectivite.error.upload_avatar', {errorCode: error.status}),
                    error.statusText)))
        }
    }

    deleteAvatar = () => {
        const {id} = this.props
        fetch(Routing.generate('sesile_main_collectiviteapi_deleteavatar', {id}), {
            credentials: 'same-origin',
            method: 'DELETE'
        })
            .then(this.handleErrors)
            .then(response => response.json())
            .then(json => {
                $("#confirm_delete").foundation('close')
                this.props.handleChange('image', json.image)
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.collectivite.error.delete_avatar', {errorCode: error.status}),
                error.statusText)))
    }

    saveCollectivite = () => {
        const {putCollectivite, id, nom, active, delete_classeur_after, editState} = this.props
        const fields = {active, delete_classeur_after}
        const validation = new Validator(fields, this.validationRules)
        if (validation.passes()) if (id) putCollectivite(id, fields)
    }

    render() {
        const {t} = this.context
        const {id, nom, image, active, siren, ozwilloId, ozwilloUrl, delete_classeur_after, handleChange, putCollectivite, editState} = this.props
        return (
            <div>
                <div className="grid-x grid-padding-x grid-padding-y panel"
                     style={{borderTop: '2px solid #2C56A2'}}>
                    <div className="cell medium-12" style={{padding: '20px'}}>
                        <div className="grid-x grid-padding-y">
                            <label className="cell medium-2 text-bold" htmlFor="nom">
                                {t('common.label.name')}
                            </label>
                            <div className="cell medium-10" id="nom">
                                {nom}
                            </div>
                        </div>
                        <div className="grid-x grid-padding-y">
                            <label className="cell medium-2 text-bold text-capitalize-first-letter" htmlFor="nom">
                                {t('common.siren')}
                            </label>
                            <div className="cell medium-10" id="nom">
                                {siren}
                            </div>
                        </div>
                        <div className="grid-x grid-padding-y">
                            <label className="cell medium-2 text-bold text-capitalize-first-letter" htmlFor="nom">
                                Ozwillo Id
                            </label>
                            <div className="cell medium-10" id="nom">
                                {ozwilloId}
                            </div>
                        </div>
                        <div className="grid-x grid-padding-y">
                            <div className="cell medium-12 text-right text-bold">
                                <a href={ozwilloUrl + "/my/organization/" + ozwilloId} target="_blank"
                                   className="button hollow ozwillo">
                                    <img src="https://services.sictiam.fr/img/favicons/sictiam/favicon-32x32.png"
                                         alt="Portail Services" className="image-button"/>
                                    Organisation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="grid-x grid-padding-x grid-padding-y panel">
                    <div className="cell">
                        <h3>Informations</h3>
                    </div>
                    <div className="medium-6 cell">
                        <div className="grid-x">
                            <InputValidation id="delete_classeur_after"
                                             type="number"
                                             className={"medium-12 cell"}
                                             labelText={`${t('admin.collectivite.classeur_delay')} *`}
                                             value={delete_classeur_after}
                                             onChange={handleChange}
                                             placeholder={t('admin.collectivite.placeholder_delay')}
                                             customErrorMessages={this.customErrorMessages.delete_classeur_after}
                                             validationRule={this.validationRules.delete_classeur_after}
                                             helpText={t('admin.collectivite.classeur_delay_help_text')}/>

                            <Switch id="active"
                                    className="cell medium-12"
                                    labelText={t('common.label.enabled')}
                                    checked={active}
                                    onChange={handleChange}
                                    activeText={t('common.label.yes')}
                                    inactiveText={t('common.label.no')}/>
                        </div>
                    </div>
                    <div className="medium-6 cell">
                        <div className="grid-x grid-padding-y" style={{overflow: "hidden", display:"-webkit-inline-box"}}>
                            <Avatar className="cell medium-12" size={200} nom={nom}
                                    fileName={image ? "/uploads/logo_coll/" + image : null}/>
                            <input type="file" accept="image/png,image/jpeg"
                                   onChange={e => this.updateAvatar(e.target.files[0])}
                                   id="upload_input" name="upload" style={{
                                fontSize: "170px",
                                width: "200px",
                                opacity: "0",
                                filter: "alpha(opacity=0)",
                                position: "relative",
                                top: "10px",
                                left: "-400px"
                            }}/>
                        </div>
                    </div>
                    {(id) &&
                    <Button id="submit-infos"
                            className="cell medium-12"
                            classNameButton="float-right"
                            onClick={this.saveCollectivite}
                            disabled={!editState}
                            labelText={t('common.button.edit_save')}/>}
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(CollectiviteInfos)