import React, { Component } from 'react'
import { func } from 'prop-types'
import Validator from 'validatorjs'
import { translate } from 'react-i18next'
import Editor from '../_components/Editor'
import { AccordionItem } from '../_components/AdminUI'
import { Button, Select } from '../_components/Form'

class CollectiviteEmailModels extends Component {

    static contextTypes = {
        t: func
    }

    validationRules = {
        message: 'required',
        textmailnew: 'required',
        textmailrefuse: 'required',
        textmailwalid: 'required',
        textcopymailnew: 'required'
    }
    state = {
        currentMail: "1"
    }

    saveCollectivite = () => {
        const {collectivite, putCollectivite} = this.props
        const fields = {
            message: collectivite.message,
            textmailnew: collectivite.textmailnew,
            textmailrefuse: collectivite.textmailrefuse,
            textmailwalid: collectivite.textmailwalid,
            textcopymailnew: collectivite.textcopymailnew,
            textcopymailwalid: collectivite.textcopymailwalid
        }
        const validation = new Validator(fields, this.validationRules)
        if (validation.passes() && collectivite.id) putCollectivite(collectivite.id, fields)
    }

    handleChange = (template, value) => {
        this.setState({currentMail: value})
    }

    render() {
        const {t} = this.context
        const {collectivite, handleChange, editState, news, valid, refus} = this.props
        const template = [{id: 1, nom: t('admin.template_mail.message')},
            {id: 2, nom: t('admin.template_mail.new')},
            {id: 3, nom: `${t('admin.template_mail.refus')}`},
            {id: 4, nom: `${t('admin.template_mail.valid')}`},
            {id: 5, nom: `${t('admin.template_mail.copy_new')}`},
            {id: 6, nom: `${t('admin.template_mail.copy_valid')}`}]
        const ListTemplate = template.map(option => <option key={`${option.id}`} value={option.id}>{option.nom}</option>)
        return (
            <div className="grid-x grid-padding-x grid-padding-y panel align-center-middle">
                <div className="cell">
                    <h3>Template e-mail</h3>
                </div>
                <div className="cell medium-12 grid-x panel align-center-middle"
                     style={{display: "flex", marginBottom: "0em", marginTop: "10px", width: "62%"}}>
                    <div style={{marginTop: "0px", marginBottom:"-10px", width: '100%', zIndex: "13"}}>
                        <label htmlFor="template_select" style={{fontSize:"1em"}}>
                            <Select
                                id="template_select"
                                value={this.state.currentMail}
                                wrapperStyle={{marginBottom: "0.65em"}}
                                onChange={this.handleChange}>
                            {ListTemplate}
                            </Select>
                        </label>
                    </div>
                </div>
                {this.state.currentMail === "1" &&
                <Editor id="message"
                        label={t('admin.collectivite.message')}
                        className="cell medium-10"
                        value={collectivite.message}
                        handleChange={handleChange}/>
                }
                {this.state.currentMail ==="2" &&
                <Editor id="textmailnew"
                        label={t('admin.collectivite.mail.new_classeur')}
                        className="cell medium-10"
                        value={collectivite.textmailnew}
                        template={news}
                        handleChange={handleChange}/>
                }
                {this.state.currentMail === "3" &&
                <Editor id="textmailrefuse"
                        label={t('admin.collectivite.mail.refused_classeur')}
                        className="cell medium-10"
                        value={collectivite.textmailrefuse}
                        template={refus}
                        handleChange={handleChange}/>
                }
                {this.state.currentMail === "4" &&
                <Editor id="textmailwalid"
                        label={t('admin.collectivite.mail.valid_classeur')}
                        className="cell medium-10"
                        value={collectivite.textmailwalid}
                        template={valid}
                        handleChange={handleChange}/>
                }
                {this.state.currentMail === "5" &&
                <Editor id="textcopymailnew"
                        label={t('admin.collectivite.mail.new_copy_classeur')}
                        className="cell medium-10"
                        value={collectivite.textcopymailnew}
                        template={news}
                        handleChange={handleChange}/>
                }
                {this.state.currentMail === "6" &&
                <Editor id="textcopymailwalid"
                        label={t('admin.collectivite.mail.valid_copy_classeur')}
                        className="cell medium-10"
                        value={collectivite.textcopymailwalid}
                        template={valid}
                        handleChange={handleChange}/>
                }
                {(collectivite.id) &&
                <Button id="submit-mails"
                        className="cell medium-12"
                        classNameButton="float-right"
                        onClick={this.saveCollectivite}
                        disabled={!editState}
                        labelText={t('common.button.edit_save')}/>}
            </div>
        )
    }
}

export default translate(['sesile'])(CollectiviteEmailModels)