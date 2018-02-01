import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import Iframe from 'react-iframe'
import { AdminDetails, AccordionContent, AccordionItem} from "../_components/AdminUI";
import Patchs from "./Patchs";
import Helps from "./Helps";

class HelpBoard extends Component {

    static contextTypes = {
        t: func
    }

    componentDidMount() {
        $("#admin-details").foundation()
    }

    render() {
        const { t } = this.context

        return (
            <AdminDetails title={t('common.help_board.title')} nom={t('common.help_board.title')}>
                <AccordionContent>
                    <Helps />
                    <Patchs />
                    <VideoContent />
                </AccordionContent>
            </AdminDetails>
        )
    }

}

export default translate(['sesile'])(HelpBoard)



const VideoContent = ({}, {t}) => {

    return(
        <AccordionItem title={t('common.help_board.title_video')} className="is-active" classNameChild="align-center">

            <Iframe url="https://www.youtube.com/embed/ms-YYoaU4PE"
                    width="560px"
                    height="315px"
                    id="youTube"
                    className="medium-8 cell"
                    display="initial"
                    position="relative"
                    allowFullScreen
            />

        </AccordionItem>
    )
}
VideoContent.contextTypes = {
    t: func
}